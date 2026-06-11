#!/bin/bash
# =============================================================
# UPLOAD & DEPLOY OTOMATIS KE HOSTINGER
# =============================================================
# File ini tinggal di root project D:\Semester 6\PA 3\Project\PA3
# Jalankan dari terminal Git Bash:  bash upload-hostinger.sh
# =============================================================

set -e  # Berhenti jika ada error

# --- Konfigurasi Disdukcapil Toba (Hostinger Premium) ---
HOSTING_HOST="sftp://srv578.hstgr.io"
HOSTING_USER="u286131991"
HOSTING_PORT="65002"
HOSTING_PATH="/home/u286131991/domains/disdukcapil-toba.com"
LOCAL_DOMAIN="disdukcapil-toba.com"
LOCAL_DIR="$(pwd)"

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   🚀 DEPLOY DISDUKCAPIL TOBA → HOSTINGER (OTOMATIS)         ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# --- Step 1: Tampilkan info ---
echo -e "${YELLOW}📁 Working directory: ${LOCAL_DIR}${NC}"
echo -e "${YELLOW}🌐 Target host: ${HOSTING_USER}@${HOSTING_HOST}:${HOSTING_PORT}${NC}"
echo -e "${YELLOW}📂 Target path: ${HOSTING_PATH}${NC}"
echo ""

# --- Step 2: Verifikasi file yang dibutuhkan ---
echo -e "${BLUE}▶ Step 1/7: Verifikasi file${NC}"
[ -f ".env.production" ] || [ -f ".env.production.hostinger" ] || { echo -e "${RED}❌ .env.production / .env.production.hostinger tidak ditemukan!${NC}"; exit 1; }
[ -f "artisan" ] || { echo -e "${RED}❌ artisan tidak ditemukan!${NC}"; exit 1; }
[ -d "public" ] || { echo -e "${RED}❌ folder public/ tidak ditemukan!${NC}"; exit 1; }
echo -e "${GREEN}✅ File-file penting ada${NC}"
echo ""

# --- Step 3: Tentukan file/folder yang akan di-EXCLUDE ---
echo -e "${BLUE}▶ Step 2/7: Build exclude list${NC}"
cat > /tmp/rsync-exclude.txt << 'EOF'
node_modules/
vendor/
.git/
tests/
storage/logs/
storage/framework/cache/
storage/framework/sessions/
storage/framework/views/
.env
.env.backup
.env.production
.DS_Store
Thumbs.db
*.log
*.swp
*.swo
.idea/
.vscode/
scripts/models/
scripts/__pycache__/
scripts/bigdata/
scripts/dataset_51.png
scripts/*.pyc
railway/
Dockerfile
docker-compose.yml
README.md
AUDIT_SWEETALERT2.md
CHANGELOG.md
Template - User Acceptance Test (UAT).xlsx
Project/
html/
2MB/
800)
agents/
alasan_penolakan/
uuid/
'required
with('login_success'
with('logout_success'
lacak_berkas_id])
routeIs('keagamaan.pernikahan.upload-berkas')
$urutanStart++
EOF
echo -e "${GREEN}✅ Exclude list dibuat${NC}"
echo ""

# --- Step 4: Cek rsync vs zip fallback ---
echo -e "${BLUE}▶ Step 3/7: Pilih metode upload${NC}"
if command -v rsync &> /dev/null; then
    UPLOAD_METHOD="rsync"
    echo -e "${GREEN}✅ Pakai rsync (recommended)${NC}"
else
    UPLOAD_METHOD="zip-sftp"
    echo -e "${YELLOW}⚠️  rsync tidak ada, pakai ZIP + SFTP${NC}"
fi
echo ""

# --- Step 5: Upload ke Hostinger ---
echo -e "${BLUE}▶ Step 4/7: Upload ke Hostinger${NC}"
if [ "$UPLOAD_METHOD" = "rsync" ]; then
    rsync -avz --progress \
        -e "ssh -p ${HOSTING_PORT}" \
        --exclude-from=/tmp/rsync-exclude.txt \
        --delete \
        "${LOCAL_DIR}/" "${HOSTING_USER}@${HOSTING_HOST}:${HOSTING_PATH}/"
else
    # Fallback: ZIP dan upload via sftp
    cd "${LOCAL_DIR}"
    # Buat zip
    echo "Membuat ZIP..."
    if command -v 7z &> /dev/null; then
        7z a -tzip -mx=5 deploy.zip . -xr@/tmp/rsync-exclude.txt -bb1
    else
        zip -r -q deploy.zip . -x@/tmp/rsync-exclude.txt
    fi

    # Upload ZIP
    echo "Upload ZIP ke Hostinger..."
    sftp -P ${HOSTING_PORT} ${HOSTING_USER}@${HOSTING_HOST} << SFTPEOF
cd ${HOSTING_PATH}
put deploy.zip
bye
SFTPEOF
fi
echo -e "${GREEN}✅ Upload selesai${NC}"
echo ""

# --- Step 6: Setup .env, storage permissions, run migrations ---
echo -e "${BLUE}▶ Step 5/7: Setup Laravel di server (via SSH)${NC}"
ssh -p ${HOSTING_PORT} ${HOSTING_USER}@${HOSTING_HOST} "bash -s" << 'SSHEOF'
set -e

HOSTING_PATH="/home/u286131991/domains/disdukcapil-toba.com"
cd "$HOSTING_PATH"

# Tentukan file .env.production.hostinger yang sudah diupload
if [ -f ".env.production.hostinger" ]; then
    # Backup .env lama (jika ada)
    [ -f ".env" ] && cp .env .env.backup.$(date +%Y%m%d%H%M%S) 2>/dev/null || true
    cp .env.production.hostinger .env
    echo "✅ .env sudah ter-setup dari .env.production.hostinger"
elif [ -f ".env.production" ]; then
    [ -f ".env" ] && cp .env .env.backup.$(date +%Y%m%d%H%M%S) 2>/dev/null || true
    cp .env.production .env
    echo "✅ .env sudah ter-setup dari .env.production"
else
    echo "❌ .env.production.hostinger / .env.production tidak ditemukan di server"
    exit 1
fi
    # Backup .env lama (jika ada)
    [ -f ".env" ] && cp .env .env.backup.$(date +%Y%m%d%H%M%S) 2>/dev/null || true
    cp .env.production .env
    echo "✅ .env sudah ter-setup dari .env.production"
else
    echo "❌ .env.production tidak ditemukan di server"
    exit 1
fi

# Pastikan folder storage & bootstrap/cache writable
mkdir -p storage/framework/sessions \
         storage/framework/views \
         storage/framework/cache \
         storage/framework/cache/data \
         storage/logs \
         storage/app/public \
         bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo "✅ Permissions OK"

# Hapus .env.production* (supaya tidak expose konfigurasi)
rm -f .env.production .env.production.hostinger

# Generate APP_KEY (jika masih kosong)
grep -q "^APP_KEY=$" .env && php artisan key:generate --force || echo "✅ APP_KEY sudah terisi"

# Clear & optimize
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear 2>/dev/null || true
echo "✅ Cache cleared"

# Run migrations (idempotent, tidak hapus data)
echo "▶ Running migrations..."
php artisan migrate --force
echo "✅ Migrations selesai"

# Storage link
php artisan storage:link 2>/dev/null || true
echo "✅ Storage linked"

# Optimize Laravel untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Optimized for production"

# Test koneksi database
php artisan tinker --execute="echo 'DB connected: ' . (DB::connection()->getPdo() ? 'YES' : 'NO');" || echo "⚠️  DB check skipped"

# Test koneksi ke OCR Railway
echo "▶ Testing OCR Railway connection..."
OCR_URL=\$(grep "^EASYOCR_API_URL=" .env | cut -d= -f2)
if [ -n "\$OCR_URL" ]; then
    HTTP_CODE=\$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "\${OCR_URL}/health" || echo "000")
    if [ "\$HTTP_CODE" = "200" ]; then
        echo "✅ OCR Railway connected: \${OCR_URL} (HTTP 200)"
    else
        echo "⚠️  OCR Railway tidak merespons: HTTP \$HTTP_CODE"
        echo "   URL: \${OCR_URL}"
    fi
fi
SSHEOF
echo -e "${GREEN}✅ Setup Laravel selesai${NC}"
echo ""

# --- Step 7: Setup .htaccess untuk redirect ke /public ---
echo -e "${BLUE}▶ Step 6/7: Setup .htaccess redirect ke /public${NC}"
ssh -p ${HOSTING_PORT} ${HOSTING_USER}@${HOSTING_HOST} "bash -s" << 'HTACCESS'
HTACCESS_PATH="/home/u286131991/domains/disdukcapil-toba.com/public_html/.htaccess"
cat > "\$HTACCESS_PATH" << 'EOF'
# Redirect ke /public (Laravel) — Hostinger shared hosting
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Disable directory listing
Options -Indexes

# Protect .env
<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json
</IfModule>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
EOF
echo "✅ .htaccess di setup di public_html"
HTACCESS
echo -e "${GREEN}✅ .htaccess selesai${NC}"
echo ""

# --- Step 8: Verifikasi end-to-end ---
echo -e "${BLUE}▶ Step 7/7: Verifikasi end-to-end${NC}"
echo "Memeriksa website: https://${LOCAL_DOMAIN}"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 30 "https://${LOCAL_DOMAIN}" 2>&1 || echo "000")
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ] || [ "$HTTP_CODE" = "301" ]; then
    echo -e "${GREEN}✅ Website merespons: HTTP $HTTP_CODE${NC}"
else
    echo -e "${YELLOW}⚠️  Website tidak merespons dengan baik: HTTP $HTTP_CODE${NC}"
    echo "   Coba cek di browser: https://${LOCAL_DOMAIN}"
fi

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✅ DEPLOYMENT SELESAI!                                    ║${NC}"
echo -e "${GREEN}╠════════════════════════════════════════════════════════════╣${NC}"
echo -e "${GREEN}║   Website:  https://${LOCAL_DOMAIN}                  ║${NC}"
echo -e "${GREEN}║   OCR API:  (check Railway dashboard)                      ║${NC}"
echo -e "${GREEN}║   Database: Hostinger MySQL                                ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
