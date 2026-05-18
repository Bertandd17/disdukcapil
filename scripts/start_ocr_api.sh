#!/bin/bash
# ========================================
#   KTP OCR Flask API Server Starter
# ========================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "========================================"
echo "  KTP OCR Flask API Server"
echo "========================================"
echo ""

# Check Python
if ! command -v python3 &> /dev/null && ! command -v python &> /dev/null; then
    echo "ERROR: Python not found!"
    exit 1
fi

PYTHON_CMD="python3"
command -v python3 &> /dev/null || PYTHON_CMD="python"

# Check dependencies
if ! $PYTHON_CMD -c "import flask" &> /dev/null; then
    echo "Installing Flask..."
    pip3 install flask werkzeug 2>/dev/null || pip install flask werkzeug 2>/dev/null
fi

if ! $PYTHON_CMD -c "import easyocr" &> /dev/null; then
    echo "ERROR: EasyOCR not installed!"
    echo "Please run: pip install -r requirements.txt"
    exit 1
fi

echo "Starting Flask API server..."
echo ""
echo "API will be available at: http://localhost:5000"
echo ""
echo "Endpoints:"
echo "  GET  http://localhost:5000/health"
echo "  POST http://localhost:5000/api/ocr/ktp"
echo "  POST http://localhost:5000/api/ocr/batch"
echo ""
echo "Press Ctrl+C to stop the server."
echo ""

# Start the server
$PYTHON_CMD easyocr_ktp.py
