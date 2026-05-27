# CHANGELOG - Perbaikan Sistem Disdukcapil Toba

## v2.0.0 - Mei 2026

### Komponen Baru Dibuat
- `resources/views/components/modal-konfirmasi.blade.php` - Komponen modal konfirmasi standar reusable
- `resources/views/components/upload-berkas.blade.php` - Komponen upload berkas dengan validasi

### Bug Fixes - BAGIAN A (Konsistensi Warna Button)
- `visimisi.blade.php`: Tombol Simpan diubah dari `bg-blue-600` → `bg-green-600` (aksi positif)
- `penerbitan_akte_kematian.blade.php`: Tombol filter diubah dari `bg-green-600` → `bg-blue-600` (aksi navigasi)

### Bug Fixes - BAGIAN B (Standar Modal)
- Semua modal hapus menggunakan SweetAlert dengan tombol "Batal" + "Konfirmasi"

### Bug Fixes - BAGIAN D (User Side)
- D1 (Encoding): Konfigurasi database sudah menggunakan utf8mb4
- D5 (NIK readonly): Field NIK sudah readonly di form layanan
- D7 (Akta Kematian feedback): Redirect dengan flash message setelah submit
- D8 (Lacak berkas): Query diperbaiki untuk mencari semua status

### Bug Fixes - BAGIAN E (Admin Side)
- E3 (Organisasi): Fitur organisasi sudah berfungsi dengan CRUD lengkap
- E6 (Modal reusable): Komponen modal-konfirmasi.blade.php sudah dibuat
- E8 (Layanan tertukar): Validasi jenis layanan di Antrian_Online_Controller
- E10 (Antrian ganda): Pengecekan antrian aktif di controller

### Perbaikan HTML
- `visimisi.blade.php`: Dihapus duplicate form penghargaan yang menempel

### Catatan Teknis
- Database charset: utf8mb4 dengan collation utf8mb4_unicode_ci
- Layout utama sudah memiliki `<meta charset="UTF-8">`
- Toast notifications menggunakan SweetAlert2 dengan format 2 baris untuk error

---
*Dibuat: Mei 2026*
