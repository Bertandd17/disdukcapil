import os
import sys
import json

# Add project root to path
project_root = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, project_root)

# Import the easyocr module
sys.path.insert(0, os.path.join(project_root, '..'))
from scripts.easyocr_ktp import process_ocr, EASYOCR_AVAILABLE

# Get image path from argument
if len(sys.argv) < 2:
    print(json.dumps({
        'status': 'error',
        'error': 'Usage: python run_ocr.py <image_path>'
    }))
    sys.exit(1)

image_path = sys.argv[1]

# Normalize path
image_path = os.path.normpath(image_path)

print(f"Testing path: {image_path}", file=sys.stderr)
print(f"Exists: {os.path.exists(image_path)}", file=sys.stderr)

if not os.path.exists(image_path):
    print(json.dumps({
        'status': 'error',
        'error': f'File not found: {image_path}'
    }))
    sys.exit(1)

if not EASYOCR_AVAILABLE:
    print(json.dumps({
        'status': 'error',
        'error': 'EasyOCR not available'
    }))
    sys.exit(1)

# Process
result = process_ocr(image_path)

# Output JSON
print(json.dumps(result, ensure_ascii=False, indent=2))
