#!/usr/bin/env python3
"""Custom CRNN OCR runtime for trained KTP models.

The Laravel app calls scripts/easyocr_ktp.py. This module is intentionally
standalone so the trained CRNN models can be used as the primary recognizer
without changing the PHP service boundary.
"""

from __future__ import annotations

import os
import re
import sys
from pathlib import Path
from typing import Any, Iterable

try:
    import cv2
    import numpy as np
except ImportError as exc:  # pragma: no cover - depends on local OCR env
    cv2 = None
    np = None
    print(f"# Warning: OpenCV/NumPy unavailable for CRNN OCR: {exc}", file=sys.stderr)

try:
    import torch
    import torch.nn as nn
except ImportError as exc:  # pragma: no cover - depends on local OCR env
    torch = None
    nn = None
    print(f"# Warning: PyTorch unavailable for CRNN OCR: {exc}", file=sys.stderr)


SCRIPT_DIR = Path(__file__).resolve().parent
PROJECT_DIR = SCRIPT_DIR.parent


def _env_path(name: str, default: Path) -> Path:
    value = os.environ.get(name)
    if not value:
        return default

    path = Path(value)
    return path if path.is_absolute() else PROJECT_DIR / path


MODELS_DIR = _env_path("KTP_OCR_MODELS_PATH", SCRIPT_DIR / "models")
STATE_DICT_PATH = _env_path("KTP_CRNN_STATE_DICT_PATH", MODELS_DIR / "ktp_crnn_v2_state_dict.pt")
TRACED_PATH = _env_path("KTP_CRNN_TRACED_PATH", MODELS_DIR / "ktp_crnn_v2_traced.pt")

DEFAULT_CHARSET = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz /"
KTP_KEYWORDS = {
    "NIK",
    "NAMA",
    "TEMPAT",
    "LAHIR",
    "ALAMAT",
    "KEL",
    "DESA",
    "KECAMATAN",
    "KABUPATEN",
    "PROVINSI",
    "AGAMA",
    "PEKERJAAN",
    "WNI",
}

_recognizer: "KtpCrnnRecognizer | None" = None


def _env_bool(name: str, default: bool = False) -> bool:
    value = os.environ.get(name)
    if value is None:
        return default
    return value.strip().lower() in {"1", "true", "yes", "y", "on"}


def _clean_text(text: str) -> str:
    text = re.sub(r"[^0-9A-Za-z /\-.,:]", "", text)
    text = re.sub(r"\s+", " ", text).strip()
    return text


if nn is not None:

    class CRNN(nn.Module):
        """Architecture reconstructed from ktp_crnn_v2_state_dict.pt keys."""

        def __init__(self, num_classes: int = 75, hidden_size: int = 128):
            super().__init__()
            self.cnn = nn.Sequential(
                nn.Conv2d(3, 64, 3, 1, 1),
                nn.BatchNorm2d(64),
                nn.ReLU(inplace=True),
                nn.Conv2d(64, 64, 3, 1, 1),
                nn.BatchNorm2d(64),
                nn.ReLU(inplace=True),
                nn.MaxPool2d((2, 2), (2, 2)),
                nn.Conv2d(64, 128, 3, 1, 1),
                nn.BatchNorm2d(128),
                nn.ReLU(inplace=True),
                nn.Conv2d(128, 128, 3, 1, 1),
                nn.BatchNorm2d(128),
                nn.ReLU(inplace=True),
                nn.MaxPool2d((2, 2), (2, 2)),
                nn.Dropout2d(0.2),
                nn.Conv2d(128, 256, 3, 1, 1),
                nn.BatchNorm2d(256),
                nn.ReLU(inplace=True),
                nn.Conv2d(256, 256, 3, 1, 1),
                nn.BatchNorm2d(256),
                nn.ReLU(inplace=True),
                nn.MaxPool2d((2, 1), (2, 1)),
                nn.Dropout2d(0.2),
                nn.Conv2d(256, 256, 3, 1, 1),
                nn.BatchNorm2d(256),
                nn.ReLU(inplace=True),
                nn.Conv2d(256, 256, 3, 1, 1),
                nn.BatchNorm2d(256),
                nn.ReLU(inplace=True),
                nn.MaxPool2d((2, 1), (2, 1)),
                nn.Dropout2d(0.2),
                nn.Conv2d(256, 256, (2, 1), 1, 0),
                nn.BatchNorm2d(256),
                nn.ReLU(inplace=True),
            )
            self.rnn = nn.LSTM(
                256,
                hidden_size,
                num_layers=1,
                batch_first=True,
                bidirectional=True,
            )
            self.dropout_fc = nn.Dropout(0.0)
            self.fc = nn.Linear(hidden_size * 2, num_classes)

        def forward(self, x):  # noqa: D401 - mirrors traced model
            features = self.cnn(x).squeeze(2)
            features = features.permute(2, 0, 1).permute(1, 0, 2)
            output, _ = self.rnn(features)
            output = self.dropout_fc(output)
            return self.fc(output).permute(1, 0, 2)


else:
    CRNN = None


class KtpCrnnRecognizer:
    def __init__(
        self,
        state_dict_path: Path = STATE_DICT_PATH,
        traced_path: Path = TRACED_PATH,
    ) -> None:
        if torch is None or nn is None or np is None or cv2 is None:
            raise RuntimeError("PyTorch, OpenCV, and NumPy are required for CRNN OCR")

        if not state_dict_path.exists():
            raise FileNotFoundError(f"CRNN state_dict not found: {state_dict_path}")

        self.state_dict_path = state_dict_path
        self.traced_path = traced_path
        requested_device = os.environ.get("KTP_CRNN_DEVICE", "").strip().lower()
        use_cuda = requested_device == "cuda" or (
            requested_device == "" and torch.cuda.is_available() and _env_bool("KTP_CRNN_USE_CUDA", True)
        )
        self.device = torch.device("cuda" if use_cuda and torch.cuda.is_available() else "cpu")
        self.models: list[tuple[str, Any]] = []
        self.model_status: dict[str, str] = {}

        checkpoint = torch.load(str(state_dict_path), map_location="cpu")
        self.charset = os.environ.get("KTP_CRNN_CHARSET") or checkpoint.get("charset") or DEFAULT_CHARSET
        self.num_classes = int(checkpoint.get("num_classes") or checkpoint.get("config", {}).get("num_classes") or 75)
        hidden_size = int(checkpoint.get("config", {}).get("hidden_size") or 128)

        state_model = CRNN(num_classes=self.num_classes, hidden_size=hidden_size)
        state_model.load_state_dict(checkpoint["model_state_dict"], strict=True)
        state_model.to(self.device).eval()
        self.models.append(("state_dict", state_model))
        self.model_status["state_dict"] = f"ready:{self.device}"

        self._load_traced_model()

    def _load_traced_model(self) -> None:
        if not self.traced_path.exists():
            self.model_status["traced"] = "missing"
            return

        try:
            traced_device = self.device
            traced = torch.jit.load(str(self.traced_path), map_location=traced_device)
            traced.eval()
            if traced_device.type == "cuda":
                traced.to(traced_device)

            # The current traced export contains cuda:0 inside LSTM state creation.
            # Warmup keeps it enabled only when it can really run.
            dummy = torch.zeros(1, 3, 32, 128, device=traced_device)
            with torch.no_grad():
                traced(dummy)

            self.models.insert(0, ("traced", traced))
            self.model_status["traced"] = f"ready:{traced_device}"
        except Exception as exc:
            self.model_status["traced"] = f"unusable:{type(exc).__name__}: {str(exc).splitlines()[0][:140]}"

    def describe(self) -> dict[str, Any]:
        return {
            "available": True,
            "device": str(self.device),
            "state_dict_path": str(self.state_dict_path),
            "traced_path": str(self.traced_path),
            "num_classes": self.num_classes,
            "charset_length": len(self.charset),
            "models": [name for name, _ in self.models],
            "model_status": self.model_status,
        }

    def recognize_crop(self, crop: Any) -> dict[str, Any]:
        tensors = self._prepare_crop_variants(crop)
        best = {"text": "", "confidence": 0.0, "model": "", "strategy": "", "score": -1.0}

        for variant_name, tensor in tensors:
            tensor = tensor.to(self.device)
            for model_name, model in self.models:
                try:
                    with torch.no_grad():
                        logits = model(tensor)
                    decoded = self._decode_logits(logits)
                    decoded["model"] = model_name
                    decoded["variant"] = variant_name
                    if decoded["score"] > best["score"]:
                        best = decoded
                except Exception as exc:
                    self.model_status[model_name] = (
                        f"runtime_error:{type(exc).__name__}: {str(exc).splitlines()[0][:140]}"
                    )

        best["text"] = _clean_text(best.get("text", ""))
        return best

    def recognize_image(
        self,
        image_path: str | Path,
        detections: Iterable[tuple[Any, str, float]] | None = None,
    ) -> dict[str, Any]:
        image = cv2.imread(str(image_path))
        if image is None:
            return {"success": False, "message": "Image not readable by OpenCV", "raw_text": ""}

        entries = self._entries_from_detections(image, detections)
        if not entries:
            entries = self._detect_regions_with_opencv(image)

        words: list[dict[str, Any]] = []
        crnn_used = 0
        support_used = 0
        for entry in entries:
            crop = entry.get("crop")
            if crop is None or crop.size == 0:
                continue

            crnn = self.recognize_crop(crop)
            support_text = _clean_text(str(entry.get("support_text") or ""))
            support_conf = float(entry.get("support_confidence") or 0.0)

            text = crnn["text"]
            confidence = float(crnn.get("confidence") or 0.0)
            source = "crnn"

            if not self._is_usable_text(text, confidence) and support_text:
                text = support_text
                confidence = support_conf
                source = "easyocr_support"
                support_used += 1
            elif text:
                crnn_used += 1

            if text:
                words.append(
                    {
                        "text": text,
                        "confidence": round(confidence, 4),
                        "source": source,
                        "bbox": entry.get("bbox"),
                        "y": entry.get("y", 0.0),
                        "x": entry.get("x", 0.0),
                        "crnn": crnn,
                    }
                )

        raw_text = self._group_words_into_lines(words)
        avg_conf = (
            sum(float(w["confidence"]) for w in words) / len(words)
            if words
            else 0.0
        )

        return {
            "success": bool(raw_text),
            "raw_text": raw_text,
            "confidence": round(avg_conf, 4),
            "words": words,
            "stats": {
                "regions": len(entries),
                "words": len(words),
                "crnn_used": crnn_used,
                "easyocr_support_used": support_used,
                "models": [name for name, _ in self.models],
                "model_status": self.model_status,
            },
        }

    def _prepare_crop_variants(self, crop: Any) -> list[tuple[str, Any]]:
        if crop is None or crop.size == 0:
            return []

        if len(crop.shape) == 2:
            rgb = cv2.cvtColor(crop, cv2.COLOR_GRAY2RGB)
        else:
            rgb = cv2.cvtColor(crop, cv2.COLOR_BGR2RGB)

        variants = [("normal", rgb)]
        if _env_bool("KTP_CRNN_TRY_INVERT", False):
            variants.append(("inverted", 255 - rgb))

        prepared = []
        target_h = int(os.environ.get("KTP_CRNN_HEIGHT", "32"))
        max_w = int(os.environ.get("KTP_CRNN_MAX_WIDTH", "512"))
        min_w = int(os.environ.get("KTP_CRNN_MIN_WIDTH", "16"))
        normalize = os.environ.get("KTP_CRNN_NORMALIZE", "zero_one").strip().lower()

        for name, img in variants:
            h, w = img.shape[:2]
            if h <= 0 or w <= 0:
                continue
            new_w = max(min_w, min(max_w, int(round(w * (target_h / h)))))
            resized = cv2.resize(img, (new_w, target_h), interpolation=cv2.INTER_CUBIC)
            arr = resized.astype("float32") / 255.0
            if normalize in {"half", "minus_one_one", "-1_1"}:
                arr = (arr - 0.5) / 0.5
            arr = np.transpose(arr, (2, 0, 1))[None, ...]
            prepared.append((name, torch.from_numpy(arr)))

        return prepared

    def _decode_logits(self, logits: Any) -> dict[str, Any]:
        if logits.dim() != 3:
            return {"text": "", "confidence": 0.0, "strategy": "invalid", "score": -1.0}

        probs = torch.softmax(logits.detach().cpu(), dim=2)
        max_probs, indices = probs.max(dim=2)
        indices = indices[:, 0].tolist()
        confidences = max_probs[:, 0].tolist()

        candidates = []
        forced_blank = os.environ.get("KTP_CRNN_BLANK_INDEX")
        strategies: list[tuple[str, int | None, int]] = []
        if forced_blank is not None and forced_blank.strip() != "":
            strategies.append(("forced_blank", int(forced_blank), 0))
        strategies.extend(
            [
                ("direct_ignore_unknown", None, 0),
                ("blank_last", self.num_classes - 1, 0),
                ("blank_zero_shifted", 0, -1),
            ]
        )

        for strategy, blank_index, shift in strategies:
            text, used_conf = self._ctc_collapse(indices, confidences, blank_index, shift)
            text = _clean_text(text)
            if not text:
                continue
            avg_conf = sum(used_conf) / len(used_conf) if used_conf else 0.0
            score = self._score_text(text, avg_conf)
            candidates.append(
                {
                    "text": text,
                    "confidence": round(avg_conf, 4),
                    "strategy": strategy,
                    "score": score,
                }
            )

        if not candidates:
            return {"text": "", "confidence": 0.0, "strategy": "empty", "score": -1.0}
        return max(candidates, key=lambda item: item["score"])

    def _ctc_collapse(
        self,
        indices: list[int],
        confidences: list[float],
        blank_index: int | None,
        shift: int,
    ) -> tuple[str, list[float]]:
        chars = []
        used_conf = []
        previous: int | None = None
        for idx, conf in zip(indices, confidences):
            if blank_index is not None and idx == blank_index:
                previous = idx
                continue
            if idx == previous:
                continue
            char_idx = idx + shift
            previous = idx
            if 0 <= char_idx < len(self.charset):
                chars.append(self.charset[char_idx])
                used_conf.append(float(conf))
        return "".join(chars), used_conf

    def _score_text(self, text: str, confidence: float) -> float:
        if not text:
            return -1.0
        readable = sum(ch.isalnum() or ch in " /-.,:" for ch in text) / max(len(text), 1)
        digit_boost = 0.12 if re.search(r"\d{5,}", text) else 0.0
        keyword_boost = 0.12 if any(keyword in text.upper() for keyword in KTP_KEYWORDS) else 0.0
        zero_blank_penalty = 0.0
        if re.search(r"[A-Za-z]", text):
            zero_ratio = text.count("0") / max(len(text), 1)
            if zero_ratio >= 0.28 or re.search(r"0[A-Za-z]0|[A-Za-z]0[A-Za-z]", text):
                zero_blank_penalty = -0.45
        length_penalty = 0.0 if len(text) >= 2 else -0.25
        return (confidence * 0.65) + (readable * 0.25) + digit_boost + keyword_boost + length_penalty + zero_blank_penalty

    def _is_usable_text(self, text: str, confidence: float) -> bool:
        if len(text.strip()) < 2:
            return False
        if confidence < float(os.environ.get("KTP_CRNN_MIN_CONFIDENCE", "0.18")):
            return False
        alnum = sum(ch.isalnum() for ch in text)
        return alnum >= 2

    def _entries_from_detections(self, image: Any, detections: Iterable[tuple[Any, str, float]] | None) -> list[dict[str, Any]]:
        if detections is None:
            return []

        entries = []
        for detection in detections:
            try:
                bbox, support_text, support_conf = detection
                crop = self._crop_bbox(image, bbox)
                xs = [float(p[0]) for p in bbox]
                ys = [float(p[1]) for p in bbox]
                entries.append(
                    {
                        "bbox": bbox,
                        "crop": crop,
                        "support_text": support_text,
                        "support_confidence": support_conf,
                        "x": min(xs),
                        "y": sum(ys) / max(len(ys), 1),
                    }
                )
            except Exception:
                continue

        return sorted(entries, key=lambda item: (item["y"], item["x"]))

    def _detect_regions_with_opencv(self, image: Any) -> list[dict[str, Any]]:
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        gray = cv2.GaussianBlur(gray, (3, 3), 0)
        binary = cv2.adaptiveThreshold(
            gray,
            255,
            cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
            cv2.THRESH_BINARY_INV,
            31,
            12,
        )
        kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (18, 3))
        dilated = cv2.dilate(binary, kernel, iterations=1)
        contours, _ = cv2.findContours(dilated, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

        h, w = image.shape[:2]
        entries = []
        for contour in contours:
            x, y, bw, bh = cv2.boundingRect(contour)
            if bw < max(18, w * 0.015) or bh < max(8, h * 0.008):
                continue
            if bw > w * 0.98 or bh > h * 0.35:
                continue
            pad_x = max(2, int(bw * 0.06))
            pad_y = max(2, int(bh * 0.25))
            x1, y1 = max(0, x - pad_x), max(0, y - pad_y)
            x2, y2 = min(w, x + bw + pad_x), min(h, y + bh + pad_y)
            crop = image[y1:y2, x1:x2]
            entries.append(
                {
                    "bbox": [[x1, y1], [x2, y1], [x2, y2], [x1, y2]],
                    "crop": crop,
                    "support_text": "",
                    "support_confidence": 0.0,
                    "x": float(x1),
                    "y": float((y1 + y2) / 2),
                }
            )

        return sorted(entries, key=lambda item: (item["y"], item["x"]))

    def _crop_bbox(self, image: Any, bbox: Any) -> Any:
        h, w = image.shape[:2]
        xs = [float(p[0]) for p in bbox]
        ys = [float(p[1]) for p in bbox]
        x1, y1 = int(max(0, min(xs))), int(max(0, min(ys)))
        x2, y2 = int(min(w, max(xs))), int(min(h, max(ys)))
        pad_x = max(2, int((x2 - x1) * 0.08))
        pad_y = max(2, int((y2 - y1) * 0.28))
        x1, y1 = max(0, x1 - pad_x), max(0, y1 - pad_y)
        x2, y2 = min(w, x2 + pad_x), min(h, y2 + pad_y)
        return image[y1:y2, x1:x2]

    def _group_words_into_lines(self, words: list[dict[str, Any]]) -> str:
        if not words:
            return ""

        words = sorted(words, key=lambda item: (float(item.get("y", 0)), float(item.get("x", 0))))
        lines: list[list[dict[str, Any]]] = []
        threshold = float(os.environ.get("KTP_CRNN_LINE_Y_THRESHOLD", "22"))
        for word in words:
            if not lines:
                lines.append([word])
                continue
            current_y = sum(float(w.get("y", 0)) for w in lines[-1]) / len(lines[-1])
            if abs(float(word.get("y", 0)) - current_y) > threshold:
                lines.append([word])
            else:
                lines[-1].append(word)

        text_lines = []
        for line in lines:
            ordered = sorted(line, key=lambda item: float(item.get("x", 0)))
            line_text = _clean_text(" ".join(str(item["text"]) for item in ordered))
            if line_text:
                text_lines.append(line_text)
        return "\n".join(text_lines)


def get_recognizer() -> KtpCrnnRecognizer | None:
    global _recognizer
    if _recognizer is not None:
        return _recognizer

    try:
        _recognizer = KtpCrnnRecognizer()
        return _recognizer
    except Exception as exc:
        print(f"# Warning: custom CRNN OCR unavailable: {exc}", file=sys.stderr)
        return None


def diagnose() -> dict[str, Any]:
    if torch is None or cv2 is None or np is None:
        return {
            "available": False,
            "reason": "missing_dependency",
            "torch": torch is not None,
            "cv2": cv2 is not None,
            "numpy": np is not None,
        }
    recognizer = get_recognizer()
    if recognizer is None:
        return {
            "available": False,
            "reason": "recognizer_load_failed",
            "state_dict_exists": STATE_DICT_PATH.exists(),
            "traced_exists": TRACED_PATH.exists(),
        }
    return recognizer.describe()


def recognize_image(
    image_path: str | Path,
    detections: Iterable[tuple[Any, str, float]] | None = None,
) -> dict[str, Any]:
    recognizer = get_recognizer()
    if recognizer is None:
        return {"success": False, "message": "Custom CRNN recognizer unavailable", "raw_text": ""}
    return recognizer.recognize_image(image_path, detections=detections)
