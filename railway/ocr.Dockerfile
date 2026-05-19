FROM python:3.11-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    EASYOCR_DOWNLOAD_ENABLED=true \
    EASYOCR_MODEL_DIR=/tmp/easyocr_models

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        libgl1 \
        libglib2.0-0 \
        libgomp1 \
    && rm -rf /var/lib/apt/lists/*

COPY scripts/requirements-ocr-railway.txt /app/scripts/requirements-ocr-railway.txt

RUN python -m pip install --upgrade pip setuptools wheel \
    && python -m pip install --no-cache-dir torch torchvision --index-url https://download.pytorch.org/whl/cpu \
    && python -m pip install --no-cache-dir -r /app/scripts/requirements-ocr-railway.txt

COPY scripts /app/scripts
COPY railway/ocr-start.sh /app/railway/ocr-start.sh

RUN mkdir -p /app/uploads /tmp/easyocr_models

EXPOSE 5000

CMD ["sh", "/app/railway/ocr-start.sh"]
