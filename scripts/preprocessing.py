#!/usr/bin/env python3
"""
Image Preprocessing Pipeline for KTP OCR
Disdukcapil Project - Anggota 2

Features:
- Image rotation correction (0°, 90°, 180°, 270°)
- Noise reduction using Gaussian blur
- Contrast enhancement using CLAHE
- Skew correction
- Image quality validation
"""

import cv2
import numpy as np
from pathlib import Path
from typing import Tuple, Optional, List
import logging

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class ImagePreprocessor:
    """Preprocessing pipeline for KTP images before OCR"""

    def __init__(self, target_size: Tuple[int, int] = (1920, 1080)):
        """
        Initialize preprocessor

        Args:
            target_size: Target size for resizing (width, height)
        """
        self.target_size = target_size

    def preprocess(self, image_path: str) -> Optional[np.ndarray]:
        """
        Full preprocessing pipeline

        Args:
            image_path: Path to input image

        Returns:
            Preprocessed image or None if failed
        """
        try:
            # Load image
            img = cv2.imread(image_path)
            if img is None:
                logger.error(f"Failed to load image: {image_path}")
                return None

            logger.info(f"Original shape: {img.shape}")

            # 1. Rotation correction
            img = self.correct_rotation(img)

            # 2. Skew correction
            img = self.correct_skew(img)

            # 3. Denoise
            img = self.denoise(img)

            # 4. Enhance contrast
            img = self.enhance_contrast(img)

            # 5. Sharpen
            img = self.sharpen(img)

            # 6. Validate quality
            if not self.validate_quality(img):
                logger.warning("Image quality is below threshold")

            logger.info(f"Final shape: {img.shape}")

            return img

        except Exception as e:
            logger.error(f"Preprocessing failed: {e}")
            return None

    def correct_rotation(self, img: np.ndarray) -> np.ndarray:
        """
        Detect and correct image rotation
        Uses text orientation detection
        """
        # Convert to grayscale
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

        # Try all 4 rotations and pick the one with most horizontal text
        angles = [0, 90, 180, 270]
        best_angle = 0
        best_score = -1

        for angle in angles:
            rotated = self.rotate_image(gray, angle)

            # Detect horizontal lines (indicating horizontal text)
            edges = cv2.Canny(rotated, 50, 150)
            horizontal_kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (25, 1))
            horizontal_lines = cv2.morphologyEx(edges, cv2.MORPH_OPEN, horizontal_kernel)

            score = np.sum(horizontal_lines)
            if score > best_score:
                best_score = score
                best_angle = angle

        if best_angle != 0:
            logger.info(f"Correcting rotation by {best_angle} degrees")
            img = self.rotate_image(img, best_angle)

        return img

    def rotate_image(self, img: np.ndarray, angle: int) -> np.ndarray:
        """Rotate image by specified angle"""
        if angle == 0:
            return img
        elif angle == 90:
            return cv2.rotate(img, cv2.ROTATE_90_CLOCKWISE)
        elif angle == 180:
            return cv2.rotate(img, cv2.ROTATE_180)
        elif angle == 270:
            return cv2.rotate(img, cv2.ROTATE_90_COUNTERCLOCKWISE)
        return img

    def correct_skew(self, img: np.ndarray) -> np.ndarray:
        """
        Correct skew/perspective distortion
        Uses document boundary detection
        """
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

        # Binary threshold
        _, binary = cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY_INV + cv2.THRESH_OTSU)

        # Find contours
        contours, _ = cv2.findContours(binary, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

        if not contours:
            return img

        # Get largest contour (likely the KTP document)
        largest_contour = max(contours, key=cv2.contourArea)

        # Get minimum area rectangle
        rect = cv2.minAreaRect(largest_contour)
        angle = rect[-1]

        # Correct the skew angle
        if angle < -45:
            angle = 90 + angle

        if abs(angle) > 2:  # Only correct if skew is significant
            logger.info(f"Correcting skew angle: {angle:.2f} degrees")
            (h, w) = img.shape[:2]
            center = (w // 2, h // 2)
            M = cv2.getRotationMatrix2D(center, angle, 1.0)
            img = cv2.warpAffine(img, M, (w, h), flags=cv2.INTER_CUBIC, borderMode=cv2.BORDER_REPLICATE)

        return img

    def denoise(self, img: np.ndarray) -> np.ndarray:
        """
        Remove noise from image
        Uses bilateral filter for edge-preserving denoising
        """
        # Convert to grayscale for denoising
        if len(img.shape) == 3:
            gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        else:
            gray = img

        # Apply bilateral filter
        denoised = cv2.bilateralFilter(gray, d=9, sigmaColor=75, sigmaSpace=75)

        # Apply median filter for salt-and-pepper noise
        denoised = cv2.medianBlur(denoised, 3)

        # Convert back to BGR if original was color
        if len(img.shape) == 3:
            denoised = cv2.cvtColor(denoised, cv2.COLOR_GRAY2BGR)

        return denoised

    def enhance_contrast(self, img: np.ndarray) -> np.ndarray:
        """
        Enhance contrast using CLAHE (Contrast Limited Adaptive Histogram Equalization)
        """
        # Convert to LAB color space
        lab = cv2.cvtColor(img, cv2.COLOR_BGR2LAB)

        # Split channels
        l, a, b = cv2.split(lab)

        # Apply CLAHE to L channel
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
        l = clahe.apply(l)

        # Merge channels
        lab = cv2.merge([l, a, b])

        # Convert back to BGR
        enhanced = cv2.cvtColor(lab, cv2.COLOR_LAB2BGR)

        return enhanced

    def sharpen(self, img: np.ndarray) -> np.ndarray:
        """
        Sharpen image using unsharp masking
        """
        gaussian = cv2.GaussianBlur(img, (0, 0), 2.0)
        sharpened = cv2.addWeighted(img, 1.5, gaussian, -0.5, 0)
        return sharpened

    def validate_quality(self, img: np.ndarray) -> bool:
        """
        Validate image quality
        Returns True if quality is acceptable
        """
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

        # Check Laplacian variance (focus measure)
        laplacian_var = cv2.Laplacian(gray, cv2.CV_64F).var()
        logger.info(f"Focus measure (Laplacian variance): {laplacian_var:.2f}")

        if laplacian_var < 50:
            logger.warning("Image may be blurry")
            return False

        return True

    def resize_if_needed(self, img: np.ndarray, max_width: int = 1920) -> np.ndarray:
        """Resize image if too large while maintaining aspect ratio"""
        h, w = img.shape[:2]

        if w > max_width:
            ratio = max_width / w
            new_h = int(h * ratio)
            img = cv2.resize(img, (max_width, new_h), interpolation=cv2.INTER_AREA)
            logger.info(f"Resized to {img.shape}")

        return img

    def binarize(self, img: np.ndarray) -> np.ndarray:
        """
        Convert to binary image for better OCR
        Uses adaptive thresholding
        """
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

        # Adaptive thresholding
        binary = cv2.adaptiveThreshold(
            gray, 255,
            cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
            cv2.THRESH_BINARY,
            31, 2
        )

        return binary


def preprocess_batch(input_dir: Path, output_dir: Path) -> List[str]:
    """
    Preprocess a batch of images

    Args:
        input_dir: Input directory containing images
        output_dir: Output directory for preprocessed images

    Returns:
        List of processed image paths
    """
    output_dir.mkdir(parents=True, exist_ok=True)

    preprocessor = ImagePreprocessor()
    processed_files = []

    image_extensions = {'.jpg', '.jpeg', '.png', '.bmp', '.tiff'}

    for img_path in input_dir.rglob('*'):
        if img_path.suffix.lower() not in image_extensions:
            continue

        logger.info(f"Processing: {img_path.name}")

        # Preprocess
        processed_img = preprocessor.preprocess(str(img_path))

        if processed_img is not None:
            # Save
            rel_path = img_path.relative_to(input_dir)
            out_path = output_dir / rel_path
            out_path.parent.mkdir(parents=True, exist_ok=True)
            cv2.imwrite(str(out_path), processed_img)
            processed_files.append(str(out_path))

    logger.info(f"Processed {len(processed_files)} images")
    return processed_files


if __name__ == "__main__":
    import argparse

    parser = argparse.ArgumentParser(description="KTP Image Preprocessing")
    parser.add_argument("input", help="Input image or directory")
    parser.add_argument("-o", "--output", help="Output path")
    parser.add_argument("--batch", action="store_true", help="Process directory as batch")

    args = parser.parse_args()

    input_path = Path(args.input)

    if args.batch and input_path.is_dir():
        output_path = Path(args.output) if args.output else input_path.parent / f"{input_path.name}_preprocessed"
        preprocess_batch(input_path, output_path)
    else:
        preprocessor = ImagePreprocessor()
        result = preprocessor.preprocess(str(input_path))

        if result is not None:
            output_path = args.output or input_path.stem + "_preprocessed" + input_path.suffix
            cv2.imwrite(output_path, result)
            logger.info(f"Saved to: {output_path}")
