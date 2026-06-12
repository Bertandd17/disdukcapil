<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Antrian_Online_Model - Model untuk tabel antrian_online
 * 
 * Alias/backward compatibility untuk AntrianOnline
 * Menggunakan tabel yang sama: antrian_online
 */
class Antrian_Online_Model extends Model
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

    // Status constants
    public const STATUS_MENUNGGU = 'Menunggu';
    public const STATUS_DIGUNAKAN = 'Digunakan';
    public const STATUS_DITERIMA = 'Diterima';
    public const STATUS_DITOLAK = 'Ditolak';
    public const STATUS_SELESAI = 'Selesai';

    /**
     * Boot method untuk auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Relasi ke tabel layanan
     */
    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan_Model::class, 'layanan_id', 'layanan_id');
    }

    /**
     * Relasi ke tabel lacak_berkas
     */
    public function lacak_berkas(): HasMany
    {
        return $this->hasMany(Lacak_Berkas_Model::class, 'antrian_online_id', 'antrian_online_id');
    }

    /**
     * Alias untuk relasi lacak_berkas (camelCase)
     */
    public function lacakBerkas(): HasMany
    {
        return $this->lacak_berkas();
    }

    /**
     * Scope untuk antrian yang menunggu
     */
    public function scopeMenunggu($query)
    {
        return $query->where('status_antrian', self::STATUS_MENUNGGU);
    }

    /**
     * Scope untuk antrian hari ini
     */
    public function scopeHariIni($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    /**
     * Scope untuk pencarian berdasarkan nomor antrian
     */
    public function scopeCariNomor($query, string $nomor)
    {
        $nomor = strtoupper(trim($nomor));
        $nomorClean = str_replace('-', '', $nomor);

        return $query->where(function($q) use ($nomor, $nomorClean) {
            $q->where('nomor_antrian', $nomor)
              ->orWhere('nomor_antrian', 'like', $nomor . '%')
              ->orWhereRaw("REPLACE(UPPER(nomor_antrian), '-', '') LIKE ?", [$nomorClean . '%']);
        });
    }

    /**
     * Scope untuk pencarian exact nomor antrian (case-insensitive)
     * Untuk MySQL dengan berbagai collation
     */
    public function scopeCariNomorExact($query, string $nomor)
    {
        $nomor = strtoupper(trim($nomor));

        return $query->where(function($q) use ($nomor) {
            // Coba exact match dengan case-insensitive
            $q->whereRaw('UPPER(nomor_antrian) = ?', [$nomor])
              // Fallback dengan LIKE (MySQL)
              ->orWhere('nomor_antrian', 'like', $nomor)
              // Fallback dengan case-sensitive exact
              ->orWhere('nomor_antrian', '=', $nomor);
        });
    }

    /**
     * Scope untuk pencarian berdasarkan nama
     */
    public function scopeCariNama($query, string $nama)
    {
        return $query->where('nama_lengkap', 'like', '%' . trim($nama) . '%');
    }

    /**
     * Scope untuk antrian yang belum digunakan (belum ada lacak_berkas)
     */
    public function scopeBelumDigunakan($query)
    {
        return $query->whereDoesntHave('lacak_berkas');
    }

    /**
     * Scope untuk antrian yang sudah digunakan (sudah ada lacak_berkas)
     */
    public function scopeSudahDigunakan($query)
    {
        return $query->whereHas('lacak_berkas');
    }

    /**
     * Cek apakah antrian sudah digunakan secara terminal.
     * Hanya status terminal (ditolak/selesai) yang TIDAK bisa dipakai lagi.
     * Status 'diterima' oleh admin TIDAK memblokir user — user tetap bisa pakai nomor
     * di form layanan; blokir hanya terjadi saat submit form (status -> 'digunakan').
     */
    public function isAlreadyUsed(): bool
    {
        $blockedStatuses = [
            self::STATUS_DITOLAK,
            self::STATUS_SELESAI,
        ];

        return $this->status_antrian && in_array($this->status_antrian, $blockedStatuses, true);
    }

    /**
     * Cek apakah user dengan NIK tertentu hari ini sudah mengajukan layanan yang sama
     * OPTIMIZED: Gunakan join langsung untuk performa lebih baik
     *
     * @param string $nik NIK user yang akan dicek
     * @param string $layananId ID layanan yang akan dicek
     * @return array ['already_submitted' => bool, 'message' => string]
     */
    public static function checkDailyLimit(string $nik, string $layananId): array
    {
        $startTime = microtime(true);
        $layanan = Layanan_Model::findByIdentifier($layananId);
        $normalizedLayananId = $layanan?->layanan_id ?? $layananId;

        // OPTIMIZED: Gunakan join langsung ke lacak_berkas daripada whereHas
        // Ini jauh lebih cepat karena tidak perlu subquery
        $exists = \DB::table('antrian_online')
            ->join('lacak_berkas', 'antrian_online.antrian_online_id', '=', 'lacak_berkas.antrian_online_id')
            ->where('antrian_online.nik', $nik)
            ->where('antrian_online.layanan_id', $normalizedLayananId)
            ->whereDate('lacak_berkas.tanggal', '=', now()->toDateString())
            ->whereNotIn('antrian_online.status_antrian', ['Ditolak', 'Dibatalkan', 'Menunggu'])
            ->whereNotIn('lacak_berkas.status', ['Menunggu'])
            ->exists();

        $elapsed = round((microtime(true) - $startTime) * 1000, 2);
        \Log::info('checkDailyLimit query', [
            'nik' => $nik,
            'layanan_id' => $normalizedLayananId,
            'exists' => $exists,
            'query_time_ms' => $elapsed
        ]);

        if ($exists) {
            $namaLayanan = $layanan ? $layanan->nama_layanan : 'layanan ini';
            $problem = "Anda sudah mengajukan {$namaLayanan} hari ini.";
            $solution = 'Satu user hanya dapat mengajukan layanan yang sama sekali dalam satu hari. Silakan coba lagi besok.';

            return [
                'already_submitted' => true,
                'message' => "Anda sudah mengajukan <strong>{$namaLayanan}</strong> hari ini. Satu user hanya dapat mengajukan layanan yang sama sekali dalam satu hari. Silakan coba lagi besok.",
                'problem' => $problem,
                'solution' => $solution,
                'error_code' => 'DAILY_LIMIT_EXCEEDED',
                'layanan_id' => $normalizedLayananId,
                'layanan_nama' => $namaLayanan,
                'nik' => $nik
            ];
        }

        return [
            'already_submitted' => false,
            'message' => 'Belum mencapai limit harian'
        ];
    }

    /**
     * Validasi antrian untuk layanan tertentu
     *
     * @param string $layananId ID layanan yang akan dicek
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validateForLayanan(string $layananId): array
    {
        $layananTujuan = Layanan_Model::findByIdentifier($layananId);

        if (!$layananTujuan) {
            return [
                'valid' => false,
                'message' => 'Layanan yang dipilih tidak ditemukan. Silakan muat ulang halaman dan pilih layanan kembali.',
                'problem' => 'Layanan yang dipilih tidak ditemukan.',
                'solution' => 'Muat ulang halaman, lalu pilih layanan kembali sebelum memasukkan nomor antrian.',
                'error_code' => 'UNKNOWN_SERVICE',
                'layanan_tujuan' => $layananId,
            ];
        }

        // Cek apakah sudah ditolak/dibatalkan (terminal status)
        if ($this->isAlreadyUsed()) {
            return [
                'valid' => false,
                'message' => 'Nomor antrian ini sudah tidak aktif (ditolak atau dibatalkan).',
                'problem' => 'Nomor antrian ini sudah tidak aktif.',
                'solution' => 'Buat nomor antrian baru di halaman Antrian Online, lalu gunakan nomor baru tersebut.',
                'error_code' => 'ALREADY_USED'
            ];
        }

        // Cek apakah layanan sesuai
        if ($this->layanan_id !== $layananTujuan->layanan_id) {
            $layananAsal = $this->relationLoaded('layanan')
                ? $this->layanan
                : Layanan_Model::findByIdentifier($this->layanan_id);

            $namaLayananAsal = $layananAsal ? $layananAsal->nama_layanan : 'layanan lain';
            $namaLayananTujuan = $layananTujuan->nama_layanan;
            $problem = "Nomor antrian ini berlaku untuk layanan {$namaLayananAsal}, bukan {$namaLayananTujuan}.";
            $solution = "Pilih layanan {$namaLayananAsal}, atau buat nomor antrian baru untuk layanan {$namaLayananTujuan}.";

            return [
                'valid' => false,
                'message' => "Nomor antrian ini hanya berlaku untuk layanan <strong>{$namaLayananAsal}</strong>. Silakan buat nomor antrian baru untuk layanan <strong>{$namaLayananTujuan}</strong>.",
                'problem' => $problem,
                'solution' => $solution,
                'error_code' => 'INVALID_SERVICE',
                'layanan_asal' => $this->layanan_id,
                'layanan_asal_nama' => $namaLayananAsal,
                'layanan_tujuan' => $layananTujuan->layanan_id,
                'layanan_tujuan_nama' => $namaLayananTujuan
            ];
        }

        return [
            'valid' => true,
            'message' => 'Nomor antrian valid'
        ];
    }
}
