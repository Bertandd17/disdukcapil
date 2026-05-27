# CLAUDE.md — Disdukcapil Toba

## RULES (Fundamental)

- Do what has been asked; nothing more, nothing less
- NEVER create files unless absolutely necessary — prefer editing existing files
- NEVER create documentation files unless explicitly requested
- NEVER save working files or tests to root — use `/src`, `/tests`, `/docs`, `/config`, `/scripts`
- ALWAYS read a file before editing it
- NEVER commit secrets, credentials, or `.env` files
- NEVER add a `Co-Authored-By` trailer to user commits unless `.claude/settings.json` has `attribution.commit` set
- Keep files under 500 lines
- Validate input at system boundaries

---

# PROJECT OVERVIEW

## Identitas Sistem

- **Nama**: Disdukcapil Toba — Sistem Informasi Kependudukan
- **Deskripsi**: Sistem pelayanan kependudukan berbasis web untuk Dinas Kependudukan dan Pencatatan Sipil yang mendukung proses antrian online, OCR dokumen kependudukan, manajemen data penduduk, dan penerbitan dokumen civil (KTP, KK, Akta Lahir, Akta Kematian, dll.)
- **Konteks**: Sistem pemerintahan daerah untuk pelayanan publik di Kabupaten Toba, Sumatera Utara
- **Lisensi**: Internal pemerintahan

## Jenis Dokumen yang Diproses

| Jenis Dokumen | Format | Status |
|---|---|---|
| KTP Elektronik | JPG, PNG, PDF | ✅ Didukung |
| Kartu Keluarga (KK) | JPG, PNG, PDF | ✅ Didukung |
| Akta Lahir | JPG, PNG, PDF | ✅ Didukung |
| Akta Perkawinan | JPG, PNG, PDF | ✅ Didukung |
| Akta Kematian | JPG, PNG, PDF | ✅ Didukung |
| Surat Pindah | JPG, PNG, PDF | 🔜 Roadmap |
| KIA (Kartu Identitas Anak) | JPG, PNG, PDF | 🔜 Roadmap |

## Target Pengguna

| Role | Deskripsi | Akses |
|---|---|---|
| `admin` | Administrator sistem | Full access, manajemen user, config |
| `keagamaan` | Petugas事务所民政 | Input pernikahan, upload berkas |
| `user` | Masyarakat (umum) | Pendaftaran antrian online, cek status |

---

# TECH STACK

## Backend

- **Framework**: Laravel 12.x
- **Bahasa**: PHP 8.2+
- **Database**: MySQL via `pdo_mysql`
- **Queue**: Laravel Queue (Redis sebagai driver opsional)
- **Cache**: Laravel Cache (file/redis)
- **Auth**: Laravel Auth + Spatie Permission (`spatie/laravel-permission`)

## Frontend

- **Template Engine**: Laravel Blade
- **CSS Framework**: Tailwind CSS 3.x
- **JavaScript**: Vanilla JS + library pihak ketiga (SweetAlert2, dll.)
- **Font**: Plus Jakarta Sans (via Google Fonts)

## OCR Engine (Multi-Provider)

| Provider | Mode | Konfigurasi |
|---|---|---|
| **EasyOCR** | API / CLI | `EASYOCR_USE_API`, `EASYOCR_API_URL` |
| **Google Vision API** | Cloud | `GOOGLE_VISION_API_KEY`, `GOOGLE_APPLICATION_CREDENTIALS` |
| **OCR.space** | Cloud | `OCR_SPACE_API_KEY`, `OCR_SPACE_ENABLED` |

**Dokumentasi OCR**: `config/services.php` → section `easyocr`, `ocrspace`, `google_vision`

## Storage

- **Local Disk**: `storage/app/` (Dokumen, uploads)
- **Disks Terkonfigurasi** (`config/filesystems.php`):
  - `local` — storage/app
  - `public` — storage/app/public
  - `private` — storage/app/private (encrypted)
  - `secure` — storage/app/secure (aksess limit)
  - `temporary` — storage/app/temporary (auto-cleanup)
- **Dokumen user**: `uploads/` (permission 0644, directory 0755)

## Security & Middleware

- `XSSProtectionMiddleware` — sanitisasi XSS
- `OcrSecurityMiddleware` — keamanan endpoint OCR
- `CameraPermissionPolicy` — kebijakan akses kamera
- `spatie/laravel-permission` — RBAC (role + permission)
- Rate Limiting per endpoint (lihat `config/security.php`)

---

# PROJECT STRUCTURE

```
PA3/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── TestEasyOcrCommand.php       # Test EasyOCR CLI
│   │       └── TestKtpOcrCommand.php        # Test KTP OCR
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── EasyOcrController.php    # EasyOCR API endpoint
│   │   │   │   ├── KtpOcrController.php      # Google Vision KTP endpoint
│   │   │   │   └── OcrController.php        # Generic OCR controller
│   │   │   └── *.php                        # (Admin, Auth, dll.)
│   │   └── Middleware/
│   │       ├── XSSProtectionMiddleware.php
│   │       ├── CameraPermissionPolicy.php
│   │       └── OcrSecurityMiddleware.php
│   ├── Models/
│   │   ├── User.php                         # User dengan UUID
│   │   ├── AntrianOnline.php                # Antrian online dengan OCR fields
│   │   ├── KartuKeluarga.php                # Model KK
│   │   ├── AkteLahir.php, AkteKematian.php, LahirMati.php
│   │   ├── LayananPernikahan.php, DokumenPernikahan.php
│   │   ├── GantiKepalaKK.php, KKHilangRusak.php, PisahKK.php
│   │   ├── LacakBerkas.php                  # Pelacakan berkas
│   │   ├── LayananModel.php, Kecamatan.php
│   │   └── Statistik*.php, Role.php, Permission.php
│   └── Services/
│       ├── EasyOcrService.php               # EasyOCR integration
│       ├── KtpOcrService.php                # Google Vision integration
│       ├── OcrSpaceService.php               # OCR.space provider
│       ├── KtpOcrParsingService.php          # KTP parsing (regex-based)
│       └── AdvancedKtpOcrParsingService.php # Advanced parsing
├── bootstrap/
├── config/
│   ├── app.php
│   ├── database.php
│   ├── auth.php
│   ├── security.php                         # Rate limiting, CSP, headers
│   ├── services.php                         # EasyOCR, Google Vision, OCR.space config
│   ├── filesystems.php
│   ├── permission.php
│   ├── cache.php
│   └── logging.php
├── database/
│   ├── migrations/                          # 40+ migration files
│   ├── factories/
│   └── seeders/
├── html/                                    # Static HTML files
├── public/
├── resources/
│   ├── views/
│   │   ├── admin/                           # Admin dashboard views
│   │   ├── auth/                            # Login, register, verify-question
│   │   ├── keagamaan/                       # Keagamaan module views
│   │   ├── layouts/                         # Layout templates (admin, keagamaan, user)
│   │   ├── components/                      # Blade components (admin, user, keagamaan)
│   │   ├── pages/                           # Public pages (index, antrian-online, statistik)
│   │   └── errors/                          # Error pages (403, 404, dll.)
│   ├── js/
│   │   ├── app.js, bootstrap.js
│   │   ├── auto-logout.js                   # Auto logout feature
│   │   └── sweetalert-disdukcapil.js        # SweetAlert config
│   └── css/
├── routes/
│   ├── api.php                              # API routes (OCR, statistik, antrian, pernikahan)
│   └── web.php
├── scripts/                                 # Python OCR scripts
│   ├── easyocr_ktp.py                       # Main EasyOCR script
│   ├── run_ocr.py                           # OCR runner
│   ├── start_ocr_api.bat / start_ocr_api.sh  # Startup scripts
├── storage/
│   ├── app/
│   │   ├── documents/                      # Stored documents
│   │   ├── private/                         # Private files
│   │   └── secure/                          # Encrypted files
│   └── framework/
│       ├── cache/
│       ├── sessions/
│       └── views/
├── tests/
│   └── Feature/
│       └── OcrTest.php                      # OCR API tests (Pest/PHPUnit)
├── uploads/                                 # User uploads (temp)
├── .env                                     # Environment variables (DO NOT COMMIT)
├── .env.example
├── composer.json
├── phpunit.xml
├── tailwind.config.js
├── Dockerfile
└── railway.json
```

### Lokasi File Konfigurasi OCR

| Config | Lokasi |
|---|---|
| EasyOCR API | `config/services.php` → `easyocr` |
| Google Vision | `config/services.php` → `google_vision` |
| OCR.space | `config/services.php` → `ocrspace` |
| Rate Limiting | `config/security.php` → `rate_limits` |
| File Upload | `config/security.php` → `file_upload` |
| Storage Disks | `config/filesystems.php` → `disks` |

### Lokasi Template/Tipe Dokumen

- OCR Services → `app/Services/` (EasyOcrService, KtpOcrService, OcrSpaceService)
- Parsing Services → `app/Services/KtpOcrParsingService.php` (KTP-specific parsing)
- Python OCR Scripts → `scripts/` (easyocr_ktp.py, run_ocr.py)

---

# CORE FEATURES & MODULES

## 1. Modul OCR (Optical Character Recognition)

**Pipeline OCR**:
1. User upload dokumen via `POST /api/ocr/upload`
2. Validasi file (tipe: JPG/PNG/PDF, ukuran: max 5MB)
3. Preprocessing: grayscale, resize, deskew
4. Eksekusi OCR via provider (EasyOCR/Google Vision/OCR.space)
5. Parsing hasil OCR → data terstruktur
6. Validasi NIK + confidence check
7. Simpan ke database + file storage

**Controllers**:
- `Api\KtpOcrController.php` — Google Vision + KTP parsing
- `Api\EasyOcrController.php` — EasyOCR integration
- `Api\OcrController.php` — Generic OCR endpoints

**Service Classes**:
- `KtpOcrParsingService.php` — Regex-based KTP field extraction
- `AdvancedKtpOcrParsingService.php` — Enhanced parsing logic

## 2. Modul Validasi Data OCR

Regex patterns yang digunakan (`app/Services/KtpOcrParsingService.php`):

```
NIK (16 digit):      /\b\d{16}\b/
Kode Provinsi:        11-94 (valid province codes Indonesia)
Tanggal:              DD-MM-YYYY atau DD/MM/YYYY
RT/RW:                RT.\s*/\s*RW.
Golongan Darah:       [ABO][\+\-]?
Status Perkawinan:    BELUM KAWIN | KAWIN | CERAI HIDUP | CERAI MATI
```

**Validasi NIK** (dari `tests/Feature/OcrTest.php`):
- Panjang: tepat 16 digit
- Digit ke-6-7: kode kab/kota (01-76)
- Digit ke-8-11: tanggal lahir (encoded gender for female: +40)
- Digit ke12-14:acak
- Digit ke15: jenis kelamin (genap=perempuan, ganjil=laki-laki)
- Digit ke16: checksum (mod 11)

## 3. Modul Manajemen Penduduk

**Models**: `User.php`, `KartuKeluarga.php`, `AkteLahir.php`, `AkteKematian.php`, `LahirMati.php`, dll.

**Fitur**:
- CRUD data kependudukan via admin panel
- Filter/search by NIK, nama, kecamatan
- Riwayat perubahan data (audit log via `StatusPerkawinanHistory`, `LacakBerkas`)
- Enkripsi data sensitif (NIK di-encrypt saat penyimpanan)

## 4. Modul Penerbitan Dokumen

**Dokumen yang diterbitkan**:
- Kartu Keluarga (KK) — `KartuKeluarga.php`, `GantiKepalaKK.php`, `KKHilangRusak.php`, `PisahKK.php`
- Akta Lahir — `AkteLahir.php`
- Akta Kematian — `AkteKematian.php`
- Lahir Mati — `LahirMati.php`
- Dokumen Pernikahan — `LayananPernikahan.php`, `DokumenPernikahan.php`

**Views**: `resources/views/admin/penerbitan_akte_*.blade.php`

## 5. Modul Antrian Layanan

**Model**: `AntrianOnline.php`

**Fitur**:
- Nomor antrian online dengan NIK validation
- Draft/finalize workflow
- Daily limit check (`GET /api/antrian/check-daily-limit`)
- Status tracking
- OCR fields: `nik`, `nama_lengkap`, `alamat`, `ocr_status`, `ocr_confidence`, dll.

**API Endpoints**:
- `GET /api/antrian/{nomor}` — Ambil data antrian
- `GET /api/antrian/check-daily-limit` — Cek batas harian
- `POST /api/pernikahan/submit` — Submit permohonan (auth required)

## 6. Modul Laporan dan Statistik

**Models**: `StatistikPenduduk.php`, `StatistikDokumen.php`, `StatistikLayananBulanan.php`, `Kecamatan.php`

**API Endpoints** (`routes/api.php`):
```
GET /api/statistik/penduduk    — Statistik penduduk
GET /api/statistik/dokumen     — Statistik dokumen
GET /api/statistik/layanan     — Statistik layanan
GET /api/statistik/kecamatan   — Data per kecamatan
```

**Views**: `resources/views/admin/statistik/`

## 7. Modul Autentikasi dan Hak Akses

**Spatie Permission Roles**:
- `admin` — Full access
- `petugas` — Loket access
- `keagamaan` — Keagamaan module
- `supervisor` — Monitoring only

**Auth Flow**:
1. Login → `AuthController`
2. Optional: Security Question verification → `verify-question.blade.php`
3. Role-based middleware → akses sesuai permission

**Middleware**: `RoleMiddleware`, `PermissionMiddleware`, `RoleOrPermissionMiddleware`

---

# OCR PIPELINE DETAIL

## Langkah-Langkah Proses OCR

```
1. Upload File
   ├── Validasi: tipe (JPG/PNG/PDF), ukuran (max 5MB)
   ├── Simpan ke disk (storage temporer)
   └── Generate file_id

2. Preprocessing Gambar (PHP/Image Intervention)
   ├── Resize: max 2048px (lebar/tinggi)
   ├── Grayscale conversion
   ├── Contrast enhancement
   ├── Noise reduction (Gaussian blur ringan)
   └── Deskew jika perlu (rotation correction)

3. Eksekusi OCR (multi-provider)
   ├── Provider Priority: EasyOCR > Google Vision > OCR.space
   ├── Fallback: jika primary gagal, coba next provider
   └── Timeout: 30 detik (konfigurasi di config/services.php)

4. Parsing Hasil
   ├── Split text per baris
   ├── Hapus noise (label headers, province names)
   ├── Ekstrak field via regex
   ├── Normalisasi nilai (uppercase, hapus noise chars)
   └── Hitung confidence per field

5. Validasi dan Simpan
   ├── Validasi NIK (16 digit, checksum, province code)
   ├── Confidence threshold: >= 0.7 → auto-accept, < 0.7 → manual review
   ├── Simpan hasil ke database (tabel sesuai tipe dokumen)
   └── Simpan file asli ke storage (secure disk)

6. Response
   ├── JSON dengan data terstruktur + confidence scores
   └── Notifikasi ke user via webhook/polling
```

## Format File yang Diterima

| Format | MIME Type | Max Size |
|---|---|---|
| JPG/JPEG | `image/jpeg` | 5MB |
| PNG | `image/png` | 5MB |
| PDF | `application/pdf` | 5MB |

## Preprocessing Detail

```php
// Di KtpOcrService.php atau EasyOcrService.php
$image = Image::make($path);
$image->resize(2048, 2048, function ($constraint) {
    $constraint->aspectRatio();
    $constraint->upsize();
});
$image->greyscale();
$image->contrast(15);
$image->sharpen(1);
```

## Post-Processing Hasil OCR

```php
// Di KtpOcrParsingService.php
private function cleanValue(string $value): string
{
    $value = preg_replace('/\s+/', ' ', $value);
    $value = str_replace([':', '.', '-', ','], ' ', $value);
    return trim($value, " \t\n\r\0\x0B:.-,");
}

// Normalisasi digit: O→0, I/l→1
private function normalizeDigits(string $value): string
{
    $cleaned = preg_replace('/\s+/', '', $value);
    $cleaned = str_replace(['O', 'o'], '0', $cleaned);
    $cleaned = str_replace(['I', 'l'], '1', '1', $cleaned);
    $cleaned = preg_replace('/\D/', '', $cleaned);
    return $cleaned;
}
```

## Confidence Score

- **Overall confidence**: Rata-rata confidence semua field (0.0 - 1.0)
- **Per-field confidence**: `field_confidence` array
- **Threshold**:
  - `>= 0.85`: High confidence — proses automatic
  - `0.70 - 0.84`: Medium confidence — proses dengan warning
  - `< 0.70`: Low confidence — perlu review manual (flag `needs_manual_review`)

## Error Handling

```php
// Di OcrController.php
if ($result['confidence'] < 0.7) {
    // Flag untuk manual review
    $antrian->update([
        'ocr_confidence' => $result['confidence'],
        'ocr_needs_review' => true,
        'ocr_provider' => $provider,
    ]);
    return response()->json([
        'success' => true,
        'warning' => 'Confidence rendah, perlu verifikasi manual',
        'data' => $result,
    ]);
}
```

## Contoh Output JSON

### Response Sukses OCR KTP:

```json
{
  "success": true,
  "data": {
    "nik": "1201011708900001",
    "nama_lengkap": "BAMBANG SUPRIYANTO",
    "tempat_lahir": "SIANTAR",
    "tanggal_lahir": "17-08-1990",
    "jenis_kelamin": "LAKI-LAKI",
    "gol_darah": "B+",
    "alamat": "JL. PENDIDIKAN NO. 12",
    "rt_rw": "RT 001/RW 005",
    "kel_desa": "SIANTAR MARToba",
    "kec": "SIANTAR",
    "kab_kota": "SIMPANG TUNJUNG",
    "provinsi": "SUMATERA UTARA",
    "agama": "ISLAM",
    "status_perkawinan": "KAWIN",
    "pekerjaan": "PNS",
    "kewarganegaraan": "WNI",
    "confidence": 0.94,
    "field_confidence": {
      "nik": 1.0,
      "nama_lengkap": 1.0,
      "tempat_lahir": 1.0,
      "tanggal_lahir": 1.0,
      "jenis_kelamin": 1.0,
      "alamat": 0.8,
      "rt_rw": 1.0,
      "kel_desa": 0.9,
      "kecamatan": 0.9,
      "kab_kota": 0.9,
      "provinsi": 1.0,
      "agama": 1.0,
      "status_perkawinan": 1.0,
      "pekerjaan": 0.8,
      "kewarganegaraan": 0.5
    }
  }
}
```

### Response Error:

```json
{
  "success": false,
  "error": {
    "code": "OCR_FAILED",
    "message": "OCR engine timeout atau provider tidak tersedia",
    "retry_after": 30
  }
}
```

---

# DATA MODELS / SCHEMA

## Tabel Utama

### `users`

| Field | Tipe | Deskripsi |
|---|---|---|
| `id` | UUID | Primary key |
| `name` | string | Nama lengkap user |
| `email` | string | Email (unique) |
| `password` | string | Hash Argon2id |
| `role` | enum | admin, petugas, keagamaan, supervisor |
| `security_questions` | JSON | Pertanyaan keamanan |
| `timestamps` | | created_at, updated_at |

### `antrian_online`

| Field | Tipe | Deskripsi |
|---|---|---|
| `id` | bigint | Primary key |
| `nomor_antrian` | string | Format: ANTMMDD-XXX |
| `nik` | string(16) | NIK (encrypted) |
| `nama_lengkap` | string | Nama dari hasil OCR/manual |
| `alamat` | text | Alamat lengkap |
| `layanan_id` | bigint | FK ke tabel layanan |
| `status` | enum | draft, submitted, processed, completed, rejected |
| `ocr_status` | enum | pending, processing, completed, failed |
| `ocr_confidence` | float | Confidence score (0.0-1.0) |
| `ocr_result` | JSON | Full OCR result |
| `created_by` | UUID | FK ke users |
| `timestamps` | | |

### `akte_lahirs`

| Field | Tipe | Deskripsi |
|---|---|---|
| `id` | bigint | Primary key |
| `nik_anak` | string(16) | NIK anak |
| `nama_lengkap` | string | Nama lengkap |
| `tempat_lahir` | string | Tempat lahir |
| `tanggal_lahir` | date | Format: YYYY-MM-DD |
| `jenis_kelamin` | enum | LAKI-LAKI, PEREMPUAN |
| `nama_ibu` | string | Nama ibu kandung |
| `nama_ayah` | string | Nama ayah kandung |
| `akta_file` | string | Path ke file scan |

### `akte_kematian`

| Field | Tipe | Deskripsi |
|---|---|---|
| `id` | bigint | Primary key |
| `nik_almarhum` | string(16) | NIK yang meninggal |
| `nama_lengkap` | string | Nama lengkap |
| `tanggal_kematian` | date | Tanggal meninggal |
| `tempat_kematian` | string | Lokasi meninggal |
| `penyebab` | string | Penyebab kematian |
| `akta_file` | string | Path ke file |

### `kartu_keluarga`

| Field | Tipe | Deskripsi |
|---|---|---|
| `id` | bigint | Primary key |
| `no_kk` | string(16) | Nomor KK |
| `kepala_keluarga_nik` | string(16) | NIK kepala keluarga |
| `alamat` | text | Alamat keluarga |
| `rt` | string(3) | RT |
| `rw` | string(3) | RW |
| `kelurahan` | string | Nama kelurahan |
| `kecamatan` | string | Nama kecamatan |
| `kota` | string | Nama kota/kabupaten |
| `kode_pos` | string(5) | Kode pos |
| `kk_file` | string | Path ke file KK scan |

### `layanan`

| Field | Tipe | Deskripsi |
|---|---|---|
| `id` | bigint | Primary key |
| `nama_layanan` | string | Nama layanan |
| `deskripsi` | text | Deskripsi layanan |
| `kategori` | enum | akta, kk, pernikahan, dll |
| `syarat` | JSON | Persyaratan layanan |
| `biaya` | decimal | Biaya (Rp) |
| `is_active` | boolean | Active status |

### `lacak_berkas`

| Field | Tipe | Deskripsi |
|---|---|---|
| `id` | bigint | Primary key |
| `permohonan_id` | bigint | FK ke permohonan |
| `permohonan_type` | string | Polymorphic type |
| `status` | enum | submitted, reviewing, approved, rejected, printed, issued |
| `keterangan` | text | Catatan/status |
| `updated_by` | UUID | User yang update |
| `timestamps` | | |

### Relasi Antar Tabel

```
users (UUID) ────< antrian_online.created_by
users ────< role (Spatie)

antrian_online ────< layanan (FK: layanan_id)
antrian_online ────< lacak_berkas

akte_lahir ────< lacak_berkas (Polymorphic)
akte_kematian ────< lacak_berkas
kartu_keluarga ────< lacak_berkas

statistik_penduduk ────< kecamatan (FK)
statistik_dokumen ────< layanan (FK)
statistik_layanan_bulanan ────< layanan (FK)
```

---

# CODING CONVENTIONS

## Standar Penamaan

| Element | Pattern | Contoh |
|---|---|---|
| Model | PascalCase | `KartuKeluarga`, `AkteLahir` |
| Controller | PascalCase + suffix `Controller` | `KtpOcrController`, `AntrianController` |
| Service | PascalCase + suffix `Service` | `KtpOcrParsingService`, `EasyOcrService` |
| Command | PascalCase + suffix `Command` | `TestKtpOcrCommand` |
| Middleware | PascalCase + suffix `Middleware` | `XSSProtectionMiddleware` |
| Table (Model) | snake_case | `antrian_online`, `akte_lahirs` |
| Variable | camelCase | `$ocrResult`, `$nikValue` |
| Method | camelCase | `extractNik()`, `parseOcrResult()` |
| Constant | SCREAMING_SNAKE_CASE | `VALID_PROVINCE_CODES`, `MAX_UPLOAD_SIZE` |
| File | kebab-case | `easyocr_ktp.py`, `start_ocr_api.sh` |
| Route | kebab-case | `antrian-online`, `penerbitan-akte` |

## Konvensi Bahasa

- **Komentar & dokumentasi internal**: Bahasa Indonesia
- **DocBlock**: Bahasa Indonesia untuk menjelaskan fungsi
- **Variable naming**: English (semantic) atau Indonesian untuk domain-specific

```php
/**
 * Ekstrak NIK dari baris hasil OCR.
 * Normalisasi O→0, I/l→1 untuk handle OCR misread.
 */
private function extractNik(array $lines): array { ... }
```

## Format Data Spesifik

### NIK (Nomor Induk Kependudukan)

```php
// Pattern: tepat 16 digit
const NIK_PATTERN = '/\b\d{16}\b/';

// Validasi checksum NIK Indonesia
function validateNikChecksum(string $nik): bool {
    // Digit ke-16 = (11 - (sisa bagi 11)) mod 10
    $weights = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 18];
    $sum = 0;
    for ($i = 0; $i < 15; $i++) {
        $sum += (int)$nik[$i] * $weights[$i];
    }
    $mod = $sum % 11;
    $checkDigit = (11 - $mod) % 11;
    return $checkDigit === (int)$nik[15];
}
```

### Nomor KK (Kartu Keluarga)

- Format: 16 digit
- Pattern: `/^\d{16}$/`
- Field di model: `no_kk`

### Format Tanggal

| Konteks | Format |
|---|---|
| Display (Blade) | `DD-MM-YYYY` |
| Database | `YYYY-MM-DD` |
| Input Form | `DD-MM-YYYY` |
| JSON Response | `YYYY-MM-DD` |

```php
// Display di Blade: Carbon format
{{ \Carbon\Carbon::parse($data->tanggal_lahir)->format('d-m-Y') }}

// Database storage
$tanggal = \Carbon\Carbon::createFromFormat('d-m-Y', '17-08-1990')->format('Y-m-d');

// Parse dari OCR result
$tanggal = \Carbon\Carbon::parse($result['tanggal_lahir'])->format('Y-m-d');
```

## Standar Response API

### Sukses:

```json
{
  "success": true,
  "data": { ... },
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 150
  }
}
```

### Error:

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Format NIK tidak valid",
    "details": {
      "field": "nik",
      "expected": "16 digit angka",
      "received": "12010117089000012"
    }
  }
}
```

## Penanganan Data Sensitif

### Enkripsi NIK:

```php
// Di AppServiceProvider atau model
use Illuminate\Support\Facades\Crypt;

// Encrypt saat simpan
$nikEncrypted = Crypt::encryptString($nik);

// Decrypt saat tampil
$nikDecrypted = Crypt::decryptString($nikEncrypted);
```

### Masking di Log:

```php
// Di app/Providers/LogServiceProvider
Log::info('OCR result received', [
    'nik_masked' => substr($nik, 0, 6) . '****' . substr($nik, -4),
    'confidence' => $result['confidence'],
]);
```

### Data di View (Blade):

```html
<!-- Jangan tampilkan NIK plain, gunakan masking -->
<span>{{ substr($penduduk->nik, 0, 6) }}********{{ substr($penduduk->nik, -4) }}</span>
```

---

# COMMANDS & SCRIPTS

## Install Dependencies

```bash
# PHP/Laravel
composer install

# Node.js/Frontend
npm install

# Python OCR (EasyOCR)
pip install easyocr opencv-python pillow numpy
```

## Menjalankan Server

```bash
# Laravel development server
php artisan serve

# Atau dengan specific host:port
php artisan serve --host=0.0.0.0 --port=8000

# Queue worker (untuk OCR processing)
php artisan queue:work redis --queue=ocr,default

# Schedule (cron untuk Laravel)
php artisan schedule:run
```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/OcrTest.php

# Run with coverage
php artisan test --coverage

# Run specific test method
php artisan test --filter=testOcrUploadValidation
```

## Database

```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration (destroy + recreate)
php artisan migrate:fresh

# Seed database
php artisan db:seed

# Create migration
php artisan make:migration create_table_name
```

## OCR Commands (Custom Artisan Commands)

```bash
# Test EasyOCR
php artisan ocr:test-easyocr /path/to/ktp_image.jpg

# Test KTP OCR (Google Vision)
php artisan ocr:test-ktp /path/to/ktp_image.jpg

# Check OCR service health
GET /api/ocr/health

# Process pending OCR queue
php artisan queue:work redis --queue=ocr
```

## OCR Python Scripts

```bash
# Start OCR API server (local)
bash scripts/start_ocr_api.sh
# atau
scripts/start_ocr_api.bat

# Run EasyOCR script directly
python scripts/easyocr_ktp.py --input path/to/image.jpg --output result.json

# Run OCR batch processing
python scripts/run_ocr.py --batch /path/to/images/ --output /path/to/results/
```

---

# ENVIRONMENT & CONFIG

## Variabel Environment Penting

```env
# Application
APP_NAME="Disdukcapil Toba"
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:xxx
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=disdukcapil
DB_USERNAME=root
DB_PASSWORD=

# Session
SESSION_LIFETIME=120
SESSION_DRIVER=file

# EasyOCR
EASYOCR_USE_API=true
EASYOCR_API_URL=http://localhost:5000
EASYOCR_TIMEOUT=30
EASYOCR_GPU=false

# Google Vision
GOOGLE_VISION_API_KEY=your_api_key_here
GOOGLE_APPLICATION_CREDENTIALS=/path/to/service-account.json

# OCR.space
OCR_SPACE_ENABLED=false
OCR_SPACE_API_KEY=your_api_key_here

# GCP KTP (mock mode)
GCP_MOCK_ENABLED=true
GCP_WEBHOOK_SECRET=your_webhook_secret

# File Storage
FILESYSTEM_DISK=local
MAX_UPLOAD_SIZE=5242880

# Security
RATE_LIMIT_API=60
RATE_LIMIT_AUTH=5
RATE_LIMIT_UPLOAD=10
RATE_LIMIT_OCR=20

# Cache
CACHE_DRIVER=file
```

## Konfigurasi OCR Engine (config/services.php)

```php
'easyocr' => [
    'use_api' => env('EASYOCR_USE_API', true),
    'api_url' => env('EASYOCR_API_URL', 'http://localhost:5000'),
    'timeout' => env('EASYOCR_TIMEOUT', 30),
    'gpu' => env('EASYOCR_GPU', false),
    'languages' => ['id', 'en'],
    'batch_size' => 1,
],

'google_vision' => [
    'api_key' => env('GOOGLE_VISION_API_KEY'),
    'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),
],

'ocrspace' => [
    'enabled' => env('OCR_SPACE_ENABLED', false),
    'api_key' => env('OCR_SPACE_API_KEY'),
    'endpoint' => 'https://api.ocr.space/parse/image',
],
```

## API Keys

- `GOOGLE_VISION_API_KEY` — Google Cloud Vision API key (enable billing)
- `OCR_SPACE_API_KEY` — OCR.space API key (free tier: 500 req/hour)
- `GOOGLE_APPLICATION_CREDENTIALS` — Path ke service account JSON untuk GCP

---

# TESTING APPROACH

## Cara Menjalankan Test

```bash
# All tests
php artisan test

# Single file
php artisan test tests/Feature/OcrTest.php

# With coverage
php artisan test --coverage --compact
```

## Lokasi File Test

```
tests/
├── Feature/
│   └── OcrTest.php                          # OCR API tests
├── Unit/
│   ├── Services/
│   │   ├── KtpOcrParsingServiceTest.php    # Parsing logic unit tests
│   │   └── EasyOcrServiceTest.php           # EasyOCR service tests
│   └── Models/
│       └── AntrianOnlineTest.php            # Model tests
└── factories/
    └── AntrianOnlineFactory.php             # Test factory
```

## OCR Test Coverage (tests/Feature/OcrTest.php)

- Health check endpoint
- Upload validation (tipe file, ukuran)
- Batch processing
- Status/result retrieval
- NIK checksum validation
- Date extraction from OCR
- Address parsing
- Confidence scoring

## Mock OCR Engine

```php
// Di tests/Feature/OcrTest.php
public function testOcrUploadReturnsParsedData(): void
{
    Storage::fake('public');

    // Mock EasyOCR response
    Http::fake([
        'localhost:5000/*' => Http::response([
            'text' => 'NIK: 1201011708900001\nNama: BAMBANG SUPRIYANTO\nTempat Lahir: SIANTAR\nTanggal Lahir: 17-08-1990\n...',
            'confidence' => 0.92,
        ], 200),
    ]);

    $response = $this->postJson('/api/ocr/v2/upload', [
        'file' => UploadedFile::fake()->image('ktp.jpg'),
        'provider' => 'easyocr',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['success', 'data' => ['nik', 'nama_lengkap']]);
}
```

## Data Dummy untuk Testing

- Folder: `tests/fixtures/ocr/`
- File: `ktp_sample_1.jpg`, `kk_sample_1.jpg`, `akta_lahir_sample_1.jpg`
- Generated via `php artisan make:factory` + faker

---

# KNOWN ISSUES & LIMITATIONS

## Keterbatasan OCR

| Issue | Severity | Workaround |
|---|---|---|
| Dokumen buram/berbayang | High | Preprocessing dengan contrast + sharpen; manual input |
| Dokumen miring (> 15°) | Medium | Auto-deskew sebelum OCR; user diminta re-upload |
| Dokumen rusak/sobek | High | Manual data entry; escalation ke petugas |
| Cahaya tidak merata | Medium | Enhance contrast; crop area teks |
| Foto KTP pudar/terang | Medium | Adaptive thresholding |

## Dokumen dengan Font Tidak Standar

- **Masalah**: KTP dengan font 非 standar ( Cetak ulang,磨损)
- **Solusi**: OCR fallback ke manual input; flag `needs_manual_review`

## Performa OCR pada Server Low-Spec

- EasyOCR local: butuh RAM 4GB+, GPU sangat membantu
- Jika tanpa GPU: gunakan Google Vision API (lebih cepat tapi biaya)
- Timeout default 30 detik — bisa naik ke 60 detik untuk dokumen kompleks

## Jenis Dokumen yang Belum Didukung

- Surat Pindah (Menunggu definisi template parsing)
- KIA (Kartu Identitas Anak)
-Ijazah (untuk validasi data)

## Workaround Umum

```php
// Fallback: OCR gagal → manual review
if ($result['confidence'] < 0.5) {
    Log::warning('OCR confidence sangat rendah', [
        'nik_masked' => maskNik($result['nik'] ?? ''),
    ]);

    // Kirim notifikasi ke admin untuk review manual
    AdminNotification::create([
        'type' => 'ocr_low_confidence',
        'data' => $result,
        'priority' => 'high',
    ]);
}
```

---

# IMPORTANT NOTES FOR CLAUDE

## ⚠️ KERAHASIAAN DATA

- **JANGAN** pernah log atau print NIK/No KK secara plain text di console/log
- Selalu gunakan masking: `substr($nik, 0, 6) . '****' . substr($nik, -4)`
- Hapus sensitive data dari log dengan: `LogServiceProvider` atau middleware

## ✓ VALIDASI WAJIB

- Validasi format NIK (16 digit, regex `/^\d{16}$/`) sebelum simpan ke database
- Validasi NIK checksum (digit ke-16 = (11 - (sum % 11)) % 11)
- Validasi tanggal lahir (range: 1900-01-01 sampai sekarang)
- Validasi kode provinsi (2 digit pertama NIK: 11-94)

## ✓ VERIFIKASI MANUAL

- **Hasil OCR HARUS selalu diverifikasi manusia** sebelum diterbitkan dokumen resmi
- Confidence < 0.7 → auto-flag `needs_manual_review = true`
- Proses penerbitan dokumen: gunakan Laravel Transaction

```php
DB::transaction(function () use ($data, $user) {
    $akte = AkteLahir::create($data);
    LacakBerkas::create([
        'permohonan_id' => $akte->id,
        'permohonan_type' => AkteLahir::class,
        'status' => 'approved',
        'updated_by' => $user->id,
    ]);
    // Log audit trail
    AuditLog::create([...]);
});
```

## ✓ AUDIT LOG

- **Setiap perubahan data kependudukan HARUS tercatat di audit log**
- Model: `LacakBerkas`, `StatusPerkawinanHistory`, `AdminNotification`
- Minimal: user_id, timestamp, action, before/after values

## ✓ REGULASI DATA KEPENDUDUKAN

- Data kependudukan = data sensitif (UU No. 24 Tahun 2013 tentang Perubahan UU No. 23 Tahun 2006)
- Enkripsi field NIK di database menggunakan Laravel Crypt
- Akses data: RBAC dengan Spatie Permission
- Log semua akses ke data sensitif
- Jangan simpan file dokumen di disk publik — gunakan storage `secure`

## ✓ ENKRIPSI PII

```php
// Di model atau accessor
protected $encrypted = ['nik', 'no_kk', 'alamat'];

protected $casts = [
    'nik' => 'encrypted',
    'no_kk' => 'encrypted',
];
```

## ✓ FILE UPLOAD SECURITY

- Validasi MIME type server-side (bukan hanya client-side)
- Scan untuk content-type spoofing
- Generate random filename saat simpan (UUID-based)
- Simpan di storage disk yang tidak publicly accessible

## ✓ RATE LIMITING

- API OCR: 20 req/min (konfigurasi di `config/security.php`)
- API Auth: 5 req/min
- Upload: 10 req/5min
- Umum: 60 req/min

---

# AGENT COMMS (Swarm Coordination)

## Named Agent Coordination

```javascript
// Pipeline: researcher → architect → coder → tester → reviewer
Agent({ prompt: "Research codebase. SendMessage findings to 'architect'.",
  subagent_type: "researcher", name: "researcher", run_in_background: true })
Agent({ prompt: "Wait for 'researcher'. Design solution. SendMessage to 'coder'.",
  subagent_type: "system-architect", name: "architect", run_in_background: true })
Agent({ prompt: "Wait for 'architect'. Implement it. SendMessage to 'tester'.",
  subagent_type: "coder", name: "coder", run_in_background: true })
Agent({ prompt: "Wait for 'coder'. Write tests. SendMessage results to 'reviewer'.",
  subagent_type: "tester", name: "tester", run_in_background: true })
Agent({ prompt: "Wait for 'tester'. Review code quality and security.",
  subagent_type: "reviewer", name: "reviewer", run_in_background: true })

SendMessage({ to: "researcher", summary: "Start", message: "[task context]" })
```

## Swarm Config

- **Topology**: hierarchical-mesh (anti-drift)
- **Max Agents**: 15
- **When to Swarm**: 3+ files, new features, cross-module refactoring, API changes, security, performance
- **When NOT to Swarm**: single file edits, 1-2 line fixes, docs updates, config changes

## Background Workers

| Worker | Trigger |
|---|---|
| `audit` | After security changes |
| `optimize` | After performance work |
| `testgaps` | After adding features |
| `document` | After API changes |

## Memory Pattern (Before Task)

```bash
npx @claude-flow/cli@latest memory search --query "[task keywords]" --namespace patterns
```

---

# CLI QUICK REFERENCE

```bash
# Development
php artisan serve                                    # Run dev server
php artisan queue:work redis --queue=ocr,default     # Run OCR queue worker

# Testing
php artisan test                                     # Run all tests
php artisan test tests/Feature/OcrTest.php           # Run OCR tests

# Database
php artisan migrate                                  # Run migrations
php artisan db:seed                                  # Seed database

# OCR Testing
php artisan ocr:test-easyocr /path/to/image.jpg      # Test EasyOCR
php artisan ocr:test-ktp /path/to/image.jpg           # Test KTP OCR

# Python OCR
bash scripts/start_ocr_api.sh                        # Start local OCR API
python scripts/easyocr_ktp.py --input image.jpg       # Run EasyOCR
```

---

# BUILD & TEST REQUIREMENT

- **ALWAYS** run tests after code changes
- **ALWAYS** verify build succeeds before committing
- Minimum coverage untuk OCR: NIK validation, parsing, confidence scoring

```bash
php artisan test && echo "All tests passed"
```