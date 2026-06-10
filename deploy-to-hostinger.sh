#!/bin/bash
set -e

# =============================================
# Disdukcapil Toba - Deploy to Hostinger
# =============================================
# Usage: bash deploy-to-hostinger.sh
# =============================================

REMOTE_USER="wawansyaban"
REMOTE_HOST="162.210.240.105"
REMOTE_PATH="/home/wawansyaban/domains/dispdukcapil.tobakab.go.id/public_html"

echo "============================================"
echo "  Deploy Disdukcapil Toba to Hostinger"
echo "============================================"

# 1. Build frontend
echo ""
echo "[1/4] Building frontend..."
npm run build

# 2. Upload to Hostinger via rsync
echo ""
echo "[2/4] Uploading files to Hostinger..."
rsync -avz \
  --exclude=.git \
  --exclude=.env \
  --exclude=.env.* \
  --exclude=DEPLOY_HOSTINGER_RAILWAY.md \
  --exclude=node_modules \
  --exclude=.claude \
  --exclude=.vscode \
  --exclude=vendor \
  --exclude=storage/*.key \
  --exclude="public/test-sweetalert-fix.html" \
  --exclude="public/*.log" \
  --exclude=".DS_Store" \
  --exclude="*.bak" \
  --exclude="Template - User Acceptance Test (UAT).xlsx" \
  --exclude="PANDUAN_SETUP_OCR.txt" \
  --exclude="CHANGELOG.md" \
  --exclude="*.md" \
  --exclude="test_e2e_signed_url.sh" \
  --exclude="scripts/models/" \
  --exclude="scripts/ktp_crnn_ocr.py" \
  --exclude="scripts/ktp_crnn_v2_*" \
  --exclude="scripts/train_model.py" \
  --exclude="scripts/dataset_51.png" \
  --exclude="scripts/bigdata/" \
  --exclude="scripts/__pycache__/" \
  --exclude="scripts/fix_encoding.py" \
  --exclude="scripts/preprocessing.py" \
  --exclude="scripts/setup_ocr.py" \
  --exclude="scripts/run_ocr.py" \
  --exclude="scripts/PANDUAN_SETUP_OCR.txt" \
  --exclude="railway/" \
  --exclude="nixpacks.toml" \
  --exclude="Procfile" \
  --exclude="runtime.txt" \
  --exclude="scripts/start_ocr_api.bat" \
  --exclude="scripts/start_ocr_api.sh" \
  --exclude="run-migration.php" \
  -e ssh \
  ./ ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}

echo ""
echo "[3/4] Running database migrations on Hostinger..."
ssh ${REMOTE_USER}@${REMOTE_HOST} "cd ${REMOTE_PATH} && php artisan migrate --force 2>&1 || true"

# 4. Setup APP_KEY if missing
echo ""
echo "[3.5] Checking APP_KEY on Hostinger..."
ssh ${REMOTE_USER}@${REMOTE_HOST} "cd ${REMOTE_PATH} && if ! grep -q '^APP_KEY=.' .env 2>/dev/null || [ -z \"\$(grep '^APP_KEY=' .env | cut -d= -f2)\" ]; then php artisan key:generate --force 2>&1; echo 'APP_KEY generated.'; else echo 'APP_KEY already set.'; fi"

# 4. Optimize on Hostinger
echo ""
echo "[4/4] Optimizing Laravel on Hostinger..."
ssh ${REMOTE_USER}@${REMOTE_HOST} "cd ${REMOTE_PATH} && php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache && php artisan storage:link 2>&1 || true"

echo ""
echo "============================================"
echo "  Deploy completed successfully!"
echo "============================================"
echo ""
echo "Next steps:"
echo "1. Setup Railway OCR service (see DEPLOY_HOSTINGER_RAILWAY.md)"
echo "2. Configure EASYOCR_API_URL & KTP_OCR_MODELS_PATH in Hostinger env"
echo "3. Test OCR flow on dispdukcapil.tobakab.go.id"
