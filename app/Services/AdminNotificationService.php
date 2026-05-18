<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AdminNotificationService
{
    /**
     * Buat notifikasi baru untuk admin
     *
     * @param string $title Judul notifikasi
     * @param string $message Pesan notifikasi
     * @param string $type Tipe layanan (kk,akte_lahir,akte_mati, dll)
     * @param string|null $link Link ke detail (optional)
     * @return int ID notifikasi yang dibuat
     */
    public static function create($title, $message, $type = 'layanan', $link = null)
    {
        $id = DB::table('admin_notifications')->insertGetId([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'notification_id');

        return $id;
    }

    /**
     * Notifikasi untuk layanan KK baru
     */
    public static function layananKKBaru($namaPemohon, $jenisLayanan, $nomorAntrian)
    {
        $jenisMapping = [
            'Perubahan Data' => 'Perubahan Data KK',
            'Ganti Kepala' => 'Ganti Kepala Keluarga',
            'Hilang Rusak' => 'KK Hilang/Rusak',
            'Pisah KK' => 'Pisah Kartu Keluarga',
        ];

        $jenis = $jenisMapping[$jenisLayanan] ?? $jenisLayanan;

        return self::create(
            "Layanan {$jenis} Baru",
            "Pemohon: {$namaPemohon}\nNo. Antrian: {$nomorAntrian}",
            'kk',
            null // Link can be added later
        );
    }

    /**
     * Notifikasi untuk layanan Akte Lahir baru
     */
    public static function layananAkteLahirBaru($namaPemohon, $nomorAntrian)
    {
        return self::create(
            "Layanan Akte Kelahiran Baru",
            "Pemohon: {$namaPemohon}\nNo. Antrian: {$nomorAntrian}",
            'akte_lahir',
            null
        );
    }

    /**
     * Notifikasi untuk layanan Akte Mati baru
     */
    public static function layananAkteMatiBaru($namaPemohon, $nomorAntrian)
    {
        return self::create(
            "Layanan Akte Kematian Baru",
            "Pemohon: {$namaPemohon}\nNo. Antrian: {$nomorAntrian}",
            'akte_mati',
            null
        );
    }

    /**
     * Notifikasi untuk antrian online baru
     */
    public static function antrianOnlineBaru($namaPemohon, $nomorAntrian)
    {
        return self::create(
            "Antrian Online Baru",
            "Pemohon: {$namaPemohon}\nNo. Antrian: {$nomorAntrian}",
            'antrian_online',
            null
        );
    }

    /**
     * Notifikasi untuk layanan pernikahan baru
     */
    public static function pernikahanBaru($namaPemohon, $nomorAntrian, $keagamaan)
    {
        return self::create(
            "Layanan Pernikahan Baru",
            "Pemohon: {$namaPemohon}\nNo. Antrian: {$nomorAntrian}\nLembaga: {$keagamaan}",
            'pernikahan',
            null
        );
    }

    /**
     * Notifikasi untuk verifikasi keagamaan pernikahan
     */
    public static function pernikahanVerifikasiKeagamaan($antrianId, $status, $alasan = null)
    {
        $statusText = $status === 'diterima' ? 'Diterima' : 'Ditolak';
        $message = "Verifikasi Keagamaan: {$statusText}";

        if ($alasan) {
            $message .= "\nAlasan: {$alasan}";
        }

        return self::create(
            "Verifikasi Pernikahan - Keagamaan",
            $message,
            'pernikahan',
            null
        );
    }

    /**
     * Notifikasi untuk verifikasi admin pernikahan
     */
    public static function pernikahanVerifikasiAdmin($antrianId, $status, $alasan = null)
    {
        $statusText = $status === 'diterima' ? 'Diterima & Diproses' : 'Ditolak';
        $message = "Verifikasi Admin: {$statusText}";

        if ($alasan) {
            $message .= "\nAlasan: {$alasan}";
        }

        return self::create(
            "Verifikasi Pernikahan - Admin",
            $message,
            'pernikahan',
            null
        );
    }

    /**
     * Notifikasi untuk pernikahan selesai (surat diterbitkan)
     */
    public static function pernikahanSelesai($antrianId, $nomorSurat)
    {
        return self::create(
            "Pernikahan Selesai",
            "Surat Pernikahan telah diterbitkan\nNo. Surat: {$nomorSurat}",
            'pernikahan',
            null
        );
    }

    /**
     * Notifikasi deadline H-7 keagamaan
     */
    public static function pernikahanDeadlineWarning($antrianId, $keagamaan, $tanggalPernikahan)
    {
        return self::create(
            "Deadline Verifikasi Pernikahan",
            "Lembaga: {$keagamaan}\nTanggal Pernikahan: {$tanggalPernikahan}\nSegera verifikasi sebelum H-7!",
            'pernikahan_deadline',
            null
        );
    }
}
