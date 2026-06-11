#!/bin/bash
# =============================================================
# SETUP HOSTINGER VIA SSH (LITE VERSION)
# =============================================================
# Gunakan ini SETELAH upload-hostinger.sh selesai
# Atau pakai ini jika Anda upload file via FTP manual
# Jalankan:  bash setup-hostinger.sh
# =============================================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

HOST="sftp://srv578.hstgr.io"
USER="u286131991"
PORT="65002"
PATH_REMOTE="/home/u286131991/domains/disdukcapil-toba.com"

echo -e "${YELLOW}🚀 Setup Hostinger (LITE)${NC}"
echo ""

ssh -p $PORT $USER@$HOST "bash -s" << 'EOF'
set -e
HOSTING_PATH="/home/u286131991/domains/disdukcapil-toba.com"
cd "$HOSTING_PATH"

# 1. Setup .env (support .env.production.hostinger yang baru atau .env.production legacy)
if [ -f ".env.production.hostinger" ]; then
    cp .env.production.hostinger .env && echo "✅ .env ter-setup dari .env.production.hostinger"
elif [ -f ".env.production" ]; then
    cp .env.production .env && echo "✅ .env ter-setup dari .env.production"
fi
rm -f .env.production .env.production.hostinger

# 2. Permissions
mkdir -p storage/framework/{sessions,views,cache,cache/data} storage/logs storage/app/public bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo "✅ Permissions OK"

# 3. APP_KEY (jika perlu)
grep -q "^APP_KEY=$" .env && php artisan key:generate --force || true

# 4. Clear cache
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear 2>/dev/null || true

# 5. Run migration
echo "▶ Migrating..."
php artisan migrate --force
echo "✅ Migration selesai"

# 6. Storage link
php artisan storage:link 2>/dev/null || true

# 7. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Laravel optimized"

# 8. Test DB
echo "▶ DB test..."
php artisan tinker --execute="echo DB::connection()->getPdo() ? 'DB OK' : 'DB FAIL';" || true

# 9. Test OCR
echo "▶ OCR test..."
OCR_URL=$(grep "^EASYOCR_API_URL=" .env | cut -d= -f2)
if [ -n "$OCR_URL" ]; then
    RESP=$(curl -s --max-time 10 "${OCR_URL}/health" 2>/dev/null)
    echo "OCR Response: $RESP"
fi
EOF

echo ""
echo -e "${GREEN}✅ Setup selesai!${NC}"
echo -e "Cek: https://disdukcapil-toba.com"
