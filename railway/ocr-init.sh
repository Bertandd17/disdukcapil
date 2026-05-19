#!/bin/sh
set -e

mkdir -p /app/uploads "${EASYOCR_MODEL_DIR:-/tmp/easyocr_models}"
