<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;use Illuminate\Support\Str;

/**
 * AntrianOnline — model antrian online.
 *
 * Sistem antrian dengan workflow:
 * Menunggu → Dokumen Diterima → Verifikasi Data → Proses Cetak → Siap Pengambilan
 *
 * Kolom utama:
 *  - nik (16 digit NIK KTP)
 *  - nama_lengkap
 *  - alamat
 *  - status_antrian (ENUM)
 *  - file_ktp_path (path gambar KTP hasil scan)
 *  - ocr_raw_text (raw text hasil OCR)
 *  - ocr_confidence (confidence score)
 *  - ocr_field_confidence (per-field confidence)
 */
class AntrianOnline extends Model
{
    use HasFactory;

    protected $table = 'antrian_online';

    protected $primaryKey = 'antrian_online_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    protected $fillable = [
        'antrian_online_id',
        'nomor_antrian',
        'nik',
        'nama_lengkap',
        'alamat',
        'layanan_id',
        'status_antrian',
        // OCR fields
        'file_ktp_path',
        'ocr_raw_text',
        'ocr_confidence',
        'ocr_field_confidence',
        'ocr_processed_at',
    ];

    protected $casts = [
        'status_antrian' => 'string',
        'ocr_confidence' => 'decimal:4',
        'ocr_field_confidence' => 'array',
        'ocr_processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUS_MENUNGGU = 'Menunggu';

    public const STATUS_DOKUMEN_DITERIMA = 'Dokumen Diterima';

    public const STATUS_VERIFIKASI = 'Verifikasi Data';

    public const STATUS_PROSES_CETAK = 'Proses Cetak';

    public const STATUS_SIAP_PENGAMBILAN = 'Siap Pengambilan';

    public const STATUS_DITOLAK = 'Ditolak';

    public const STATUS_DIBATALKAN = 'Dibatalkan';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->antrian_online_id)) {
                $model->antrian_online_id = (string) Str::uuid();
            }
        });
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan_Model::class, 'layanan_id', 'layanan_id');
    }

    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->where('status_antrian', self::STATUS_MENUNGGU);
    }

    /**
     * Scope untuk mencari berdasarkan NIK.
     */
    public function scopeByNik(Builder $query, string $nik): Builder
    {
        return $query->where('nik', trim($nik));
    }

    /**
     * Scope untuk antrian yang sudah di-OCR.
     */
    public function scopeWithOcrData(Builder $query): Builder
    {
        return $query->whereNotNull('ocr_processed_at');
    }

    /**
     * Scope untuk antrian dengan confidence tinggi.
     */
    public function scopeHighConfidence(Builder $query, float $threshold = 0.8): Builder
    {
        return $query->where('ocr_confidence', '>=', $threshold);
    }

    /**
     * Cek apakah data OCR sudah ada.
     */
    public function hasOcrData(): bool
    {
        return $this->ocr_processed_at !== null;
    }

    /**
     * Get confidence level.
     */
    public function getConfidenceLevel(): string
    {
        $confidence = $this->ocr_confidence ?? 0;
        
        if ($confidence >= 0.8) {
            return 'high';
        }
        
        if ($confidence >= 0.5) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get confidence color untuk UI.
     */
    public function getConfidenceColor(): string
    {
        $level = $this->getConfidenceLevel();
        
        return match ($level) {
            'high' => 'green',
            'medium' => 'yellow',
            'low' => 'red',
            default => 'gray',
        };
    }

    /**
     * Update data dari hasil OCR.
     */
    public function updateFromOcr(array $ocrData, array $extra = []): void
    {
        $this->nik = $ocrData['nik'] ?? $this->nik;
        $this->nama_lengkap = $ocrData['nama_lengkap'] ?? $this->nama_lengkap;
        $this->alamat = $ocrData['alamat'] ?? $this->alamat;
        
        // Update confidence jika ada
        if (isset($ocrData['confidence'])) {
            $this->ocr_confidence = $ocrData['confidence'];
        }
        
        if (isset($ocrData['field_confidence'])) {
            $this->ocr_field_confidence = $ocrData['field_confidence'];
        }
        
        if (isset($extra['file_ktp_path'])) {
            $this->file_ktp_path = $extra['file_ktp_path'];
        }
        
        if (isset($extra['ocr_raw_text'])) {
            $this->ocr_raw_text = $extra['ocr_raw_text'];
        }
        
        $this->ocr_processed_at = now();
        
        // Update status jika data valid
        if (!empty($this->nik) && !empty($this->nama_lengkap)) {
            $this->status_antrian = self::STATUS_DOKUMEN_DITERIMA;
        }
        
        $this->save();
    }
}
