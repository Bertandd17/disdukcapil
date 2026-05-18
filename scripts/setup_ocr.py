#!/usr/bin/env python3
"""
Setup Script for OCR Environment
Disdukcapil Project - Anggota 1

Usage:
    python setup_ocr.py --install    # Install all dependencies
    python setup_ocr.py --check      # Check installation
    python setup_ocr.py --test       # Test with sample image
"""

import os
# Fix OpenMP duplicate library conflict (PyTorch + OpenCV)
os.environ['KMP_DUPLICATE_LIB_OK'] = 'TRUE'

import sys
import subprocess
import argparse
from pathlib import Path

# Colors for terminal output
class Colors:
    GREEN = '\033[92m'
    YELLOW = '\033[93m'
    RED = '\033[91m'
    BLUE = '\033[94m'
    END = '\033[0m'

def print_info(msg):
    print(f"{Colors.BLUE}[INFO]{Colors.END} {msg}")

def print_success(msg):
    print(f"{Colors.GREEN}[SUCCESS]{Colors.END} {msg}")

def print_warning(msg):
    print(f"{Colors.YELLOW}[WARNING]{Colors.END} {msg}")

def print_error(msg):
    print(f"{Colors.RED}[ERROR]{Colors.END} {msg}")

def check_python_version():
    """Check Python version (minimum 3.10)"""
    print_info("Checking Python version...")
    version = sys.version_info
    if version.major >= 3 and version.minor >= 10:
        print_success(f"Python {version.major}.{version.minor}.{version.micro} detected")
        return True
    else:
        print_error(f"Python 3.10+ required, found {version.major}.{version.minor}.{version.micro}")
        return False

def install_dependencies():
    """Install all required dependencies"""
    print_info("Installing dependencies...")

    requirements_file = Path(__file__).parent / "requirements.txt"
    if not requirements_file.exists():
        print_error("requirements.txt not found!")
        return False

    try:
        subprocess.run(
            [sys.executable, "-m", "pip", "install", "-r", str(requirements_file)],
            check=True,
            capture_output=False
        )
        print_success("Dependencies installed successfully!")
        return True
    except subprocess.CalledProcessError as e:
        print_error(f"Failed to install dependencies: {e}")
        return False

def check_installation():
    """Check if all dependencies are installed"""
    print_info("Checking installation...")

    packages = [
        ("torch", "PyTorch"),
        ("cv2", "OpenCV"),
        ("PIL", "Pillow"),
        ("easyocr", "EasyOCR"),
        ("numpy", "NumPy"),
    ]

    all_installed = True
    for module, name in packages:
        try:
            __import__(module)
            print_success(f"{name} installed")
        except ImportError:
            print_error(f"{name} NOT installed")
            all_installed = False

    if all_installed:
        print_success("All dependencies are ready!")
    else:
        print_warning("Some dependencies are missing. Run with --install")

    return all_installed

def test_easyocr():
    """Test EasyOCR with a simple text extraction"""
    print_info("Testing EasyOCR...")

    try:
        import easyocr
        import numpy as np
        from PIL import Image, ImageDraw, ImageFont

        # Create a test image
        print_info("Creating test image...")
        img = Image.new('RGB', (200, 50), color='white')
        draw = ImageDraw.Draw(img)
        draw.text((10, 10), "TEST EASYOCR", fill='black')

        test_image_path = Path(__file__).parent / "test_ocr.jpg"
        img.save(test_image_path)

        # Run EasyOCR
        print_info("Running EasyOCR...")
        reader = easyocr.Reader(['en'], gpu=False)
        result = reader.readtext(str(test_image_path))

        if result:
            print_success("EasyOCR test successful!")
            for detection in result:
                bbox, text, confidence = detection
                print_info(f"Detected: '{text}' (confidence: {confidence:.2f})")
            return True
        else:
            print_warning("No text detected (but OCR is working)")
            return True

    except Exception as e:
        print_error(f"EasyOCR test failed: {e}")
        return False
    finally:
        # Cleanup test image
        if test_image_path.exists():
            test_image_path.unlink()

def main():
    parser = argparse.ArgumentParser(description="OCR Setup Script")
    parser.add_argument("--install", action="store_true", help="Install dependencies")
    parser.add_argument("--check", action="store_true", help="Check installation")
    parser.add_argument("--test", action="store_true", help="Test EasyOCR")
    parser.add_argument("--all", action="store_true", help="Run all steps")

    args = parser.parse_args()

    if not any([args.install, args.check, args.test, args.all]):
        parser.print_help()
        return

    print("=" * 60)
    print(" OCR Environment Setup - Disdukcapil Project")
    print("=" * 60)
    print()

    if not check_python_version():
        return

    print()

    if args.all or args.install:
        if not install_dependencies():
            return
        print()

    if args.all or args.check:
        check_installation()
        print()

    if args.all or args.test:
        test_easyocr()
        print()

    print("=" * 60)
    print(" Setup Complete!")
    print("=" * 60)

if __name__ == "__main__":
    main()
