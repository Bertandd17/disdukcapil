#!/bin/sh
set -e

mkdir -p /app/uploads "${EASYOCR_MODEL_DIR:-/tmp/easyocr_models}"

exec gunicorn \
    --chdir /app/scripts \
    --bind "0.0.0.0:${PORT:-5000}" \
    --timeout "${OCR_GUNICORN_TIMEOUT:-180}" \
    --workers "${OCR_GUNICORN_WORKERS:-1}" \
    easyocr_ktp:app
