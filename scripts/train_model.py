#!/usr/bin/env python3
r"""
KTP Rotation Classifier Training
Disdukcapil Project - Anggota 3

Melatih CNN (ResNet18) untuk klasifikasi orientasi KTP (0, 90, 180, 270 derajat).
Model hasil training dipakai untuk auto-rotate KTP sebelum diproses EasyOCR,
sehingga akurasi OCR meningkat untuk gambar yang ter-rotasi.

Struktur dataset yang dibutuhkan:
    <data_dir>/
        train/{0,90,180,270}/*.jpg
        val/{0,90,180,270}/*.jpg
        test/{0,90,180,270}/*.jpg   (opsional)

Usage:
    python train_model.py --data "C:\Users\USER\ocr\dataset" --epochs 10
    python train_model.py --test --checkpoint models/ktp_rotation_best.pth --data "..."
"""

import os
# Fix OpenMP duplicate library conflict (PyTorch + OpenCV bundle libiomp5md.dll)
os.environ['KMP_DUPLICATE_LIB_OK'] = 'TRUE'

import sys
import json
import argparse
import logging
from pathlib import Path
from datetime import datetime

try:
    import torch
    import torch.nn as nn
    import torch.optim as optim
    from torch.utils.data import Dataset, DataLoader
    from torchvision import transforms, models
    from PIL import Image
    from tqdm import tqdm
except ImportError as e:
    print(f"Missing dependency: {e}")
    print("Run: pip install -r requirements.txt")
    sys.exit(1)


logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


# ============================================================
#  Dataset
# ============================================================

class KTPRotationDataset(Dataset):
    """Dataset KTP dengan label rotasi (0, 90, 180, 270)."""

    ROTATION_LABELS = {'0': 0, '90': 1, '180': 2, '270': 3}
    ROTATION_DEGREES = [0, 90, 180, 270]

    def __init__(self, root_dir: str, split: str = 'train', transform=None):
        self.root_dir = Path(root_dir)
        self.split = split
        self.transform = transform
        self.samples = []
        self._load_samples()

    def _load_samples(self):
        split_dir = self.root_dir / self.split
        if not split_dir.exists():
            logger.warning(f"Split directory not found: {split_dir}")
            return

        for rotation_folder in sorted(split_dir.iterdir()):
            if not rotation_folder.is_dir():
                continue
            label_name = rotation_folder.name
            if label_name not in self.ROTATION_LABELS:
                logger.warning(f"Skipping unknown rotation folder: {label_name}")
                continue
            label_idx = self.ROTATION_LABELS[label_name]
            for img_path in rotation_folder.iterdir():
                if img_path.suffix.lower() in {'.jpg', '.jpeg', '.png', '.bmp', '.webp'}:
                    self.samples.append((str(img_path), label_idx))

        logger.info(f"Loaded {len(self.samples)} samples for {self.split} split")

    def __len__(self):
        return len(self.samples)

    def __getitem__(self, idx):
        path, label = self.samples[idx]
        try:
            img = Image.open(path).convert('RGB')
        except Exception as e:
            logger.error(f"Failed to load {path}: {e}")
            img = Image.new('RGB', (224, 224), color='black')

        if self.transform:
            img = self.transform(img)

        return img, label


# ============================================================
#  Model
# ============================================================

def build_model(num_classes: int = 4, pretrained: bool = True) -> nn.Module:
    """ResNet18 dimodifikasi untuk klasifikasi 4 kelas rotasi."""
    if pretrained:
        try:
            weights = models.ResNet18_Weights.DEFAULT
            model = models.resnet18(weights=weights)
        except Exception:
            model = models.resnet18(pretrained=True)
    else:
        model = models.resnet18(weights=None)

    in_features = model.fc.in_features
    model.fc = nn.Linear(in_features, num_classes)
    return model


# ============================================================
#  Trainer
# ============================================================

class RotationTrainer:
    def __init__(self, data_dir, output_dir='models', batch_size=8,
                 num_workers=0, learning_rate=1e-4, epochs=10, device=None):
        self.data_dir = Path(data_dir)
        self.output_dir = Path(output_dir)
        self.batch_size = batch_size
        self.num_workers = num_workers
        self.learning_rate = learning_rate
        self.epochs = epochs

        if device is None:
            self.device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')
        else:
            self.device = torch.device(device)
        logger.info(f"Using device: {self.device}")

        self.output_dir.mkdir(parents=True, exist_ok=True)

        self.model = None
        self.optimizer = None
        self.criterion = nn.CrossEntropyLoss()
        self.train_loader = None
        self.val_loader = None
        self.history = {'train_loss': [], 'val_loss': [],
                        'train_acc': [], 'val_acc': []}

    def setup_data(self):
        train_tf = transforms.Compose([
            transforms.Resize((224, 224)),
            transforms.ColorJitter(brightness=0.2, contrast=0.2),
            transforms.ToTensor(),
            transforms.Normalize(mean=[0.485, 0.456, 0.406],
                                 std=[0.229, 0.224, 0.225]),
        ])
        val_tf = transforms.Compose([
            transforms.Resize((224, 224)),
            transforms.ToTensor(),
            transforms.Normalize(mean=[0.485, 0.456, 0.406],
                                 std=[0.229, 0.224, 0.225]),
        ])

        train_ds = KTPRotationDataset(str(self.data_dir), 'train', train_tf)
        val_ds = KTPRotationDataset(str(self.data_dir), 'val', val_tf)

        if len(train_ds) == 0:
            raise RuntimeError(f"No training samples found in {self.data_dir / 'train'}")
        if len(val_ds) == 0:
            logger.warning("No validation samples found - validation will be skipped")

        self.train_loader = DataLoader(
            train_ds, batch_size=self.batch_size, shuffle=True,
            num_workers=self.num_workers, pin_memory=(self.device.type == 'cuda')
        )
        if len(val_ds) > 0:
            self.val_loader = DataLoader(
                val_ds, batch_size=self.batch_size, shuffle=False,
                num_workers=self.num_workers, pin_memory=(self.device.type == 'cuda')
            )

    def setup_model(self):
        logger.info("Building ResNet18 rotation classifier...")
        self.model = build_model(num_classes=4, pretrained=True).to(self.device)
        self.optimizer = optim.Adam(self.model.parameters(), lr=self.learning_rate)
        logger.info("Model ready")

    def train_epoch(self, epoch):
        self.model.train()
        total_loss, correct, total = 0.0, 0, 0
        pbar = tqdm(self.train_loader, desc=f"Epoch {epoch+1}/{self.epochs} [train]")

        for batch_idx, (images, labels) in enumerate(pbar):
            images = images.to(self.device)
            labels = labels.to(self.device)

            self.optimizer.zero_grad()
            outputs = self.model(images)
            loss = self.criterion(outputs, labels)
            loss.backward()
            self.optimizer.step()

            total_loss += loss.item()
            _, preds = outputs.max(1)
            total += labels.size(0)
            correct += preds.eq(labels).sum().item()

            pbar.set_postfix({
                'loss': f'{total_loss / (batch_idx + 1):.4f}',
                'acc': f'{100. * correct / max(total, 1):.2f}%',
            })

        return total_loss / max(len(self.train_loader), 1), 100. * correct / max(total, 1)

    @torch.no_grad()
    def validate(self):
        if self.val_loader is None:
            return 0.0, 0.0
        self.model.eval()
        total_loss, correct, total = 0.0, 0, 0
        for images, labels in tqdm(self.val_loader, desc="Validating"):
            images = images.to(self.device)
            labels = labels.to(self.device)
            outputs = self.model(images)
            loss = self.criterion(outputs, labels)
            total_loss += loss.item()
            _, preds = outputs.max(1)
            total += labels.size(0)
            correct += preds.eq(labels).sum().item()
        return total_loss / max(len(self.val_loader), 1), 100. * correct / max(total, 1)

    def save_checkpoint(self, epoch, is_best=False):
        checkpoint = {
            'epoch': epoch,
            'model_state_dict': self.model.state_dict(),
            'optimizer_state_dict': self.optimizer.state_dict(),
            'history': self.history,
            'classes': KTPRotationDataset.ROTATION_DEGREES,
        }
        if is_best:
            path = self.output_dir / 'ktp_rotation_best.pth'
        else:
            path = self.output_dir / f'ktp_rotation_epoch_{epoch+1}.pth'
        torch.save(checkpoint, path)
        logger.info(f"Checkpoint saved: {path}")

    def save_history(self):
        path = self.output_dir / 'training_history.json'
        with open(path, 'w') as f:
            json.dump(self.history, f, indent=2)
        logger.info(f"History saved: {path}")

    def train(self):
        logger.info("Starting training...")
        best_val_loss = float('inf')
        for epoch in range(self.epochs):
            train_loss, train_acc = self.train_epoch(epoch)
            val_loss, val_acc = self.validate()

            self.history['train_loss'].append(train_loss)
            self.history['train_acc'].append(train_acc)
            self.history['val_loss'].append(val_loss)
            self.history['val_acc'].append(val_acc)

            logger.info(
                f"Epoch {epoch+1}/{self.epochs} | "
                f"Train loss {train_loss:.4f} acc {train_acc:.2f}% | "
                f"Val loss {val_loss:.4f} acc {val_acc:.2f}%"
            )

            if self.val_loader is not None and val_loss < best_val_loss:
                best_val_loss = val_loss
                self.save_checkpoint(epoch, is_best=True)

            if (epoch + 1) % 10 == 0:
                self.save_checkpoint(epoch, is_best=False)

        # Selalu simpan checkpoint terakhir
        self.save_checkpoint(self.epochs - 1, is_best=(self.val_loader is None))
        self.save_history()
        logger.info("Training completed!")

    def load_checkpoint(self, checkpoint_path):
        logger.info(f"Loading checkpoint: {checkpoint_path}")
        ckpt = torch.load(checkpoint_path, map_location=self.device)
        if self.model is None:
            self.setup_model()
        self.model.load_state_dict(ckpt['model_state_dict'])
        if 'optimizer_state_dict' in ckpt and self.optimizer is not None:
            self.optimizer.load_state_dict(ckpt['optimizer_state_dict'])
        self.history = ckpt.get('history', self.history)

    @torch.no_grad()
    def test(self, checkpoint_path=None):
        test_tf = transforms.Compose([
            transforms.Resize((224, 224)),
            transforms.ToTensor(),
            transforms.Normalize(mean=[0.485, 0.456, 0.406],
                                 std=[0.229, 0.224, 0.225]),
        ])
        test_ds = KTPRotationDataset(str(self.data_dir), 'test', test_tf)
        if len(test_ds) == 0:
            logger.error("No test samples found")
            return

        test_loader = DataLoader(test_ds, batch_size=self.batch_size,
                                 shuffle=False, num_workers=self.num_workers)

        if self.model is None:
            self.setup_model()
        if checkpoint_path:
            self.load_checkpoint(checkpoint_path)

        self.model.eval()
        correct, total = 0, 0
        per_class = {i: {'correct': 0, 'total': 0} for i in range(4)}

        for images, labels in tqdm(test_loader, desc="Testing"):
            images = images.to(self.device)
            labels = labels.to(self.device)
            outputs = self.model(images)
            _, preds = outputs.max(1)
            total += labels.size(0)
            correct += preds.eq(labels).sum().item()
            for lbl, pr in zip(labels.cpu().numpy(), preds.cpu().numpy()):
                per_class[int(lbl)]['total'] += 1
                if lbl == pr:
                    per_class[int(lbl)]['correct'] += 1

        overall = 100. * correct / max(total, 1)
        logger.info(f"Test accuracy: {overall:.2f}%  ({correct}/{total})")
        for cls_idx, deg in enumerate(KTPRotationDataset.ROTATION_DEGREES):
            stats = per_class[cls_idx]
            acc = 100. * stats['correct'] / max(stats['total'], 1)
            logger.info(f"  Rotation {deg} deg: {acc:.2f}% "
                        f"({stats['correct']}/{stats['total']})")

        results = {
            'overall_accuracy': overall,
            'per_class': {str(KTPRotationDataset.ROTATION_DEGREES[i]): per_class[i]
                          for i in range(4)},
            'timestamp': datetime.now().isoformat(),
        }
        out_path = self.output_dir / 'test_results.json'
        with open(out_path, 'w') as f:
            json.dump(results, f, indent=2)
        logger.info(f"Test results saved: {out_path}")


# ============================================================
#  CLI
# ============================================================

def main():
    parser = argparse.ArgumentParser(description="KTP Rotation Classifier")
    parser.add_argument("--data", type=str, default=r"C:\Users\USER\ocr\dataset",
                        help="Path to dataset directory")
    parser.add_argument("--output", type=str, default="models",
                        help="Output directory for checkpoints")
    parser.add_argument("--batch-size", type=int, default=8)
    parser.add_argument("--epochs", type=int, default=10)
    parser.add_argument("--lr", type=float, default=1e-4)
    parser.add_argument("--num-workers", type=int, default=0,
                        help="DataLoader workers (use 0 on Windows to avoid issues)")
    parser.add_argument("--device", type=str, default=None,
                        help="cuda or cpu (auto-detect by default)")
    parser.add_argument("--test", action="store_true",
                        help="Run testing only (requires --checkpoint)")
    parser.add_argument("--checkpoint", type=str, default=None,
                        help="Path to checkpoint .pth")

    args = parser.parse_args()

    trainer = RotationTrainer(
        data_dir=args.data,
        output_dir=args.output,
        batch_size=args.batch_size,
        num_workers=args.num_workers,
        learning_rate=args.lr,
        epochs=args.epochs,
        device=args.device,
    )

    if args.test:
        if not args.checkpoint:
            logger.error("--test mode requires --checkpoint path")
            sys.exit(1)
        trainer.test(checkpoint_path=args.checkpoint)
    else:
        trainer.setup_data()
        trainer.setup_model()
        trainer.train()


if __name__ == "__main__":
    main()
