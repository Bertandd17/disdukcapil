# 📋 PANDUAN DEPLOYMENT LENGKAP
## Disdukcapil Toba - Website Disdukcapil

**Last Updated:** 2025
**Version:** 1.0.0
**Status:** ✅ Production Ready

---

## 🎯 Arsitektur Deployment

```
┌────────────────────────────────────────────────────────────────┐
│                  USER (Browser)                                │
│                  disdukcapil-toba.c-...                         │
└────────────────────────┬───────────────────────────────────────┘
                         │
                         ▼
┌────────────────────────────────────────────────────────────────┐
│  HOSTINGER (Shared Hosting)                                    │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  🌐 Website (Laravel 11)                                │  │
│  │  - public_html/                                         │  │
│  │  - PHP 8.2+ + Node.js 18                                │  │
│  │  - SSL: Let's Encrypt                                   │  │
│  │  - URL: disdukcapil-toba.c-...                          │  │
│  └────────────────┬─────────────────────────────────────────┘  │
│                   │                                            │
│  ┌────────────────▼─────────────────────────────────────────┐  │
│  │  🗄️ MySQL Database                                       │  │
│  │  - Host: localhost                                       │  │
│  │  - DB: u123456_disdukcapil                               │  │
│  │  - Tables: 25 tables (users, antrian, berkas, dll)       │  │
│  └──────────────────────────────────────────────────────────┘  │
└────────────────────────┬───────────────────────────────────────┘
                         │ HTTPS API Call (OCR)
                         ▼
┌────────────────────────────────────────────────────────────────┐
│  🚂 RAILWAY (Cloud)                                            │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  🤖 Python OCR Service (Flask API)                      │  │
│  │  - Runtime: Python 3.11                                 │  │
│  │  - Framework: Flask + EasyOCR + KTP-CRNN                │  │
│  │  - RAM: 4GB, CPU: 2 vCPU                                │  │
│  │  - URL: https://*.up.railway.app                        │  │
│  │                                                          │  │
│  │  Endpoints:                                              │  │
│  │  - GET  /health                                         │  │
│  │  - POST /api/ocr/ktp                                    │  │
│  │  - POST /api/ocr/batch                                  │  │
│  └──────────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────┘
```

---

## 📊 Status Deployment

| Komponen | Platform | Status | URL/Path |
|----------|----------|--------|----------|
| **Source Code** | GitHub | ✅ Pushed | https://github.com/Bertandd17/disdukcapil |
| **Website** | Hostinger | ⏳ Need Deploy | `public_html/` |
| **Database** | Hostinger | ⏳ Need Migrate | MySQL via phpMyAdmin |
| **OCR Engine** | Railway | ⏳ Need Deploy | `scripts/easyocr_ktp.py` |
| **SSL** | Hostinger | ⏳ Need Activate | Let's Encrypt |

---

## 🚀 LANGKAH DEPLOYMENT

### FASE 1: Setup Railway (OCR Engine) ⏱️ 15-20 menit

#### 1.1 Persiapan
- [ ] Login ke [Railway.app](https://railway.app)
- [ ] Sign in dengan GitHub account

#### 1.2 Create Project
1. Klik **"New Project"**
2. Pilih **"Deploy from GitHub repo"**
3. Pilih repository: `Bertandd17/disdukcapil`
4. Klik **"Deploy Now"**

#### 1.3 Konfigurasi Root Directory
1. Klik service yang baru dibuat
2. Tab **"Settings"** → **"Build"**
3. **Root Directory:** set ke `scripts` (PENTING!)
4. **Watch Patterns:** biarkan default

#### 1.4 Setup Environment Variables
Tab **"Variables"**, tambahkan:

```bash
# OCR Configuration
EASYOCR_LANGS=id,en
EASYOCR_DOWNLOAD_ENABLED=false
EASYOCR_USE_GPU=false
EASYOCR_MODEL_DIR=/app/models/easyocr_models
KTP_CRNN_ENABLED=true
KTP_OCR_MODELS_PATH=/app/models
KTP_CRNN_STATE_DICT_PATH=/app/models/ktp_crnn_v2_state_dict.pt
KTP_CRNN_TRACED_PATH=/app/models/ktp_crnn_v2_traced.pt
EASYOCR_DETECTOR_MODEL_PATH=/app/models/easyocr_models/craft_mlt_25k.pth
EASYOCR_RECOGNIZER_MODEL_PATH=/app/models/easyocr_models/latin_g2.pth

# Webhook Security
GCP_WEBHOOK_SECRET=<random-string-32-chars>
```

#### 1.5 Konfigurasi Build & Start
Tab **"Settings"** → **"Deploy"**:

- **Builder:** NIXPACKS (otomatis terdeteksi)
- **Healthcheck Path:** `/health`
- **Healthcheck Timeout:** `300`
- **Restart Policy:** ON_FAILURE

#### 1.6 Generate Domain
1. Tab **"Settings"** → **"Networking"**
2. Klik **"Generate Domain"**
3. Copy domain: mis. `disdukcapil-ocr-production.up.railway.app`

#### 1.7 Test OCR Service
Buka browser atau curl:
```bash
curl https://disdukcapil-ocr-production.up.railway.app/health
```

Expected response:
```json
{
  "status": "ok",
  "service": "KTP OCR",
  "version": "4.0",
  "primary": "ktp_crnn_v2",
  "crnn": {"available": true},
  "easyocr_support": true,
  "image_libs": true
}
```

#### 1.8 Simpan URL OCR
```
RAILWAY_OCR_URL=https://disdukcapil-ocr-production.up.railway.app
```

---

### FASE 2: Setup Hostinger (Website + Database) ⏱️ 30-45 menit

#### 2.1 Login Hostinger
1. Login ke [hPanel](https://hpanel.hostinger.com)
2. Pilih domain: **disdukcapil-toba**

#### 2.2 Setup Database
1. Sidebar → **"Databases"** → **"MySQL Databases"**
2. Klik **"Create New Database"**
3. Catat informasi:
   ```
   Database Name: u123456_disdukcapil
   Username:     u123456_admin
   Password:     <password-anda>
   Host:         localhost
   Port:         3306
   ```

#### 2.3 Import Schema
1. Klik **"Manage"** pada database → buka **phpMyAdmin**
2. Pilih database `u123456_disdukcapil`
3. Tab **"Import"** → pilih file `database/schema.sql` (dari repo)
4. Klik **"Go"**

**Atau via SSH Terminal Hostinger:**
```bash
mysql -u u123456_admin -p u123456_disdukcapil < database/schema.sql
```

#### 2.4 Upload Website Files
**Opsi A: Via File Manager (hPanel)**
1. Sidebar → **"Files"** → **"File Manager"**
2. Masuk ke folder `public_html/`
3. **Hapus** file default (jika ada)
4. **Upload** semua file dari folder repo **KECUALI**:
   - ❌ `node_modules/`
   - ❌ `vendor/` (install di server)
   - ❌ `scripts/`
   - ❌ `tests/`
   - ❌ `.env` (akan dibuat manual)
   - ❌ `.git/`

**Opsi B: Via Git (Lebih Praktis)**
```bash
# Di terminal SSH Hostinger
cd ~/public_html
git clone https://github.com/Bertandd17/disdukcapil.git .
# Atau jika sudah ada, pull
git pull origin main
```

#### 2.5 Install Dependencies
```bash
cd ~/public_html
composer install --optimize-autoloader --no-dev
npm ci --production
npm run build
```

#### 2.6 Setup .env File
Buat file `.env` di root `public_html/`:
```bash
APP_NAME="Disdukcapil Toba"
APP_ENV=production
APP_KEY=base64:XXXXX  # generate dengan: php artisan key:generate
APP_DEBUG=false
APP_URL=https://disdukcapil-toba.c-...

# Database Hostinger
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456_disdukcapil
DB_USERNAME=u123456_admin
DB_PASSWORD=<password-anda>

# OCR Service Railway
EASYOCR_USE_API=true
EASYOCR_API_URL=https://disdukcapil-ocr-production.up.railway.app
EASYOCR_API_HEALTH_CHECK=true
EASYOCR_API_HEALTH_TIMEOUT=15
EASYOCR_CLI_ENABLED=false
EASYOCR_SCRIPT_PATH=
EASYOCR_PYTHON_PATH=
EASYOCR_TIMEOUT=60

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

# Mail
MAIL_MAILER=log

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error
```

#### 2.7 Generate App Key
```bash
php artisan key:generate
```

#### 2.8 Set Permissions
```bash
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
```

#### 2.9 Storage Link
```bash
php artisan storage:link
```

#### 2.10 Optimize
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

#### 2.11 Run Migrations
```bash
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder  # jika ada
```

#### 2.12 Setup Cron Job (Hostinger)
1. hPanel → **"Advanced"** → **"Cron Jobs"**
2. Add new cron job:
   ```
   * * * * * cd ~/public_html && php artisan schedule:run >> /dev/null 2>&1
   ```

#### 2.13 Setup SSL
1. hPanel → **"Security"** → **"SSL"**
2. Pilih domain: `disdukcapil-toba.c-...`
3. Klik **"Install SSL"** (Let's Encrypt - FREE)
4. Tunggu 5-10 menit
5. Force HTTPS: **ON**

#### 2.14 Konfigurasi Domain
1. hPanel → **"Domains"** → **"Manage"**
2. Pastikan domain pointing ke `public_html/`
3. Cek **"Force HTTPS Redirect"** aktif

---

### FASE 3: Koneksi Website ↔ OCR ⏱️ 5-10 menit

#### 3.1 Verifikasi OCR bisa diakses dari Hostinger
SSH ke Hostinger, jalankan:
```bash
curl -X POST https://disdukcapil-ocr-production.up.railway.app/api/ocr/ktp \
  -F "image=@/tmp/test-ktp.jpg"
```

#### 3.2 Test dari Website
1. Buka browser: `https://disdukcapil-toba.c-...`
2. Login dengan admin
3. Coba fitur upload KTP
4. Verifikasi OCR berjalan

#### 3.3 Setup Webhook Secret Match
Pastikan `GCP_WEBHOOK_SECRET` di Railway **SAMA** dengan di `.env` Hostinger:
```bash
# Di Hostinger .env
GCP_WEBHOOK_SECRET=<same-secret>
```

---

### FASE 4: Post-Deployment ⏱️ 15 menit

#### 4.1 Setup Admin User
```bash
php artisan tinker
>>> \App\Models\User::create([
    'name' => 'Administrator',
    'email' => 'admin@disdukcapil-toba.go.id',
    'password' => bcrypt('password-aman-anda'),
    'role' => 'admin',
    'email_verified_at' => now()
]);
>>> exit
```

#### 4.2 Backup Otomatis
Setup backup database di hPanel:
1. **"Databases"** → **"Backups"**
2. Aktifkan **"Auto Backup"** (weekly)

#### 4.3 Monitoring
1. **Railway:** Tab **"Metrics"** untuk CPU/RAM usage
2. **Hostinger:** hPanel **"Website"** → **"Resource Usage"**
3. **Logs:**
   - Railway: Tab **"Deployments"** → **"View Logs"**
   - Hostinger: `storage/logs/laravel.log`

#### 4.4 Custom Domain (Optional)
Jika Anda punya domain sendiri:
1. hPanel → **"Domains"** → **"Add Domain"**
2. Update DNS di registrar
3. Aktifkan SSL

---

## 🔧 Konfigurasi Penting

### Multi-Provider OCR (Fallback Chain)
File: `app/Services/KtpOcrService.php`

Order fallback:
1. **Primary:** `ktp_crnn_v2` (PyTorch trained model) → Railway
2. **Secondary:** EasyOCR → Railway
3. **Tertiary:** Google Vision API → External (jika dikonfigurasi)
4. **Last:** OCR.space → External (jika dikonfigurasi)

### Security Checklist
- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] HTTPS forced
- [ ] Database password kuat
- [ ] GCP_WEBHOOK_SECRET random 32+ chars
- [ ] File permissions: 755/775
- [ ] .env tidak di-commit
- [ ] Backup otomatis aktif

### Performance
- [ ] OPcache aktif (Hostinger default: ON)
- [ ] Composer autoloader optimized
- [ ] Config/route/view cached
- [ ] NPM production build
- [ ] Railway health check aktif

---

## 🆘 TROUBLESHOOTING

### ❌ Website blank / 500 error
```bash
cd ~/public_html
php artisan cache:clear
php artisan config:clear
php artisan view:clear
tail -50 storage/logs/laravel.log
```

### ❌ Database connection error
```bash
php artisan db:show
# Cek .env DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
```

### ❌ OCR tidak jalan
1. Cek Railway service running
2. Test health: `curl https://<railway-url>/health`
3. Cek `EASYOCR_API_URL` di `.env` Hostinger
4. Cek Railway logs

### ❌ Assets (CSS/JS) tidak load
```bash
npm run build
php artisan storage:link
```

### ❌ Migration error
```bash
php artisan migrate:fresh --force
# Import ulang schema.sql
```

### ❌ Railway "Out of Memory"
- Upgrade plan Railway (min 4GB RAM untuk model ML)
- Atau set `EASYOCR_USE_GPU=false` dan model lebih kecil

---

## 📞 Quick Reference

| Service | URL | Login |
|---------|-----|-------|
| **Website** | https://disdukcapil-toba.c-... | - |
| **Hostinger hPanel** | https://hpanel.hostinger.com | Email Anda |
| **phpMyAdmin** | hPanel → Databases | DB credentials |
| **Railway** | https://railway.app | GitHub OAuth |
| **GitHub Repo** | https://github.com/Bertandd17/disdukcapil | - |

---

## 💰 Estimasi Biaya

| Item | Provider | Biaya |
|------|----------|-------|
| Hosting | Hostinger | Sudah aktif ✓ |
| Domain | Hostinger | Sudah aktif ✓ |
| Database | Hostinger | Included ✓ |
| OCR Service | Railway Free Tier | $0 (500 jam/bulan) |
| OCR Service | Railway Pro | $5/bln (unlimited) |
| **Total** | | **$0 - $5/bln** |

---

## ✅ Final Checklist

- [ ] Git push: ✅ Commit `8c3560f` pushed
- [ ] Railway: ⏳ Setup service
- [ ] Hostinger: ⏳ Upload files + .env
- [ ] Database: ⏳ Import schema
- [ ] SSL: ⏳ Activate
- [ ] OCR Test: ⏳ Verify endpoint
- [ ] Domain: ⏳ Pointing
- [ ] Backup: ⏳ Activate auto backup
- [ ] Admin User: ⏳ Create
- [ ] Cron Job: ⏳ Setup

---

**Status Saat Ini:** 🟡 Siap Deploy (Semua konfigurasi sudah ter-push ke GitHub)
**Next Step:** Setup Railway service (FASE 1)
