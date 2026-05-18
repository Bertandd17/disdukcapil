@echo off
echo ========================================
echo   KTP OCR Flask API Server
echo ========================================
echo.

cd /d "%~dp0"

REM Check if Python is available
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Python not found!
    echo Please install Python and try again.
    pause
    exit /b 1
)

REM Check if Flask is installed
python -c "import flask" >nul 2>&1
if %errorlevel% neq 0 (
    echo Installing Flask...
    pip install flask werkzeug
)

REM Check if EasyOCR is installed
python -c "import easyocr" >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: EasyOCR not installed!
    echo Please run: pip install -r requirements.txt
    pause
    exit /b 1
)

echo Starting Flask API server...
echo.
echo API will be available at: http://localhost:5000
echo.
echo Endpoints:
echo   GET  http://localhost:5000/health
echo   POST http://localhost:5000/api/ocr/ktp
echo   POST http://localhost:5000/api/ocr/batch
echo.
echo Press Ctrl+C to stop the server.
echo.

python easyocr_ktp.py
