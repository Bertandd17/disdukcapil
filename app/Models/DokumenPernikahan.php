<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DokumenPernikahan — model dokumen pendukung layanan pernikahan.
 *
 * Jenis dokumen:
 * - surat_keterangan: Surat keterangan dari gereja/lembaga keagamaan
 * - ktp_mempelai_pria: KTP mempelai pria
 * - ktp_mempelai_wanita: KTP mempelai wanita
 * - kk_mempelai_pria: Kartu Keluarga mempelai pria
 * - kk_mempelai_wanita: Kartu Keluarga mempelai wanita
 * - surat_ijin_orang_tua: Surat izin orang tua (jika diperlukan)
 * - surat_n1_n2_n4: Surat N1, N2, N4
 * - lainnya: Dokumen pendukung lainnya
 */
class DokumenPernikahan extends Model
{
    use HasFactory;

    protected $table = 'dokumen_pernikahan';

    protected $fillable = [
        'pernikahan_id',
        'jenis_dokumen',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'status',
        'catatan_verifikasi',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUS_UPLOADED = 'UPLOADED';
    public const STATUS_DIVERIFIKASI = 'DIVERIFIKASI';
    public const STATUS_DITOLAK = 'DITOLAK';

    public const JENIS_DOKUMEN = [
        'surat_keterangan' => 'Surat Keterangan',
        'ktp_mempelai_pria' => 'KTP Mempelai Pria',
        'ktp_mempelai_wanita' => 'KTP Mempelai Wanita',
        'kk_mempelai_pria' => 'Kartu Keluarga Mempelai Pria',
        'kk_mempelai_wanita' => 'Kartu Keluarga Mempelai Wanita',
        'surat_ijin_orang_tua' => 'Surat Izin Orang Tua',
        'surat_n1_n2_n4' => 'Surat N1, N2, N4',
        'foto_prewedding' => 'Foto Prewedding',
        'bukti_pembayaran' => 'Bukti Pembayaran',
        'lainnya' => 'Dokumen Lainnya',
    ];

    public function pernikahan(): BelongsTo
    {
        return $this->belongsTo(LayananPernikahan::class, 'pernikahan_id', 'pernikahan_id');
    }

    public function getJenisDokumenLabelAttribute(): string
    {
        return self::JENIS_DOKUMEN[$this->jenis_dokumen] ?? $this->jenis_dokumen;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_UPLOADED => 'Diupload',
            self::STATUS_DIVERIFIKASI => 'Diverifikasi',
            self::STATUS_DITOLAK => 'Ditolak',
            default => 'Unknown',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_UPLOADED => 'yellow',
            self::STATUS_DIVERIFIKASI => 'green',
            self::STATUS_DITOLAK => 'red',
            default => 'gray',
        };
    }

    public function getFileSizeInMbAttribute(): float
    {
        return round($this->file_size / 1024 / 1024, 2);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
