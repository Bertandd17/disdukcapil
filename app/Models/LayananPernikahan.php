<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * LayananPernikahan — model layanan pencatatan perkawinan.
 *
 * Workflow status:
 * MENUNGGU_KONFIRMASI_KEAGAMAAN → DITOLAK_KEAGAMAAN / MENUNGGU_APPROVE_TANGGAL
 * → TANGGAL_DITOLAK / TANGGAL_DISETUJUI → DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI
 * → DOKUMEN_PERLU_PERBAIKAN / DOKUMEN_DIVERIFIKASI → SELESAI
 */
class LayananPernikahan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'layanan_pernikahan';

    protected $primaryKey = 'pernikahan_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'pernikahan_id',
        'nomor_antrian',
        'user_id',
        'nama_pemohon',
        'nik_pemohon',
        'alamat_pemohon',
        'nama_mempelai_pria',
        'nik_mempelai_pria',
        'tempat_lahir_mempelai_pria',
        'tanggal_lahir_mempelai_pria',
        'agama_mempelai_pria',
        'alamat_mempelai_pria',
        'pekerjaan_mempelai_pria',
        'nama_mempelai_wanita',
        'nik_mempelai_wanita',
        'tempat_lahir_mempelai_wanita',
        'tanggal_lahir_mempelai_wanita',
        'agama_mempelai_wanita',
        'alamat_mempelai_wanita',
        'pekerjaan_mempelai_wanita',
        'nama_ayah_pria',
        'nik_ayah_pria',
        'tempat_lahir_ayah_pria',
        'tanggal_lahir_ayah_pria',
        'alamat_ayah_pria',
        'nama_ibu_pria',
        'nik_ibu_pria',
        'tempat_lahir_ibu_pria',
        'tanggal_lahir_ibu_pria',
        'alamat_ibu_pria',
        'nama_saksi_1',
        'nik_saksi_1',
        'tempat_lahir_saksi_1',
        'tanggal_lahir_saksi_1',
        'alamat_saksi_1',
        'nama_saksi_2',
        'nik_saksi_2',
        'tempat_lahir_saksi_2',
        'tanggal_lahir_saksi_2',
        'alamat_saksi_2',
        'keagamaan_id',
        'nama_gereja',
        'tanggal_perkawinan',
        'status',
        'catatan_keagamaan',
        'catatan_admin',
        'alasan_ditolak',
        'file_berkas_acara',
        'file_surat_keterangan',
        // File KTP
        'file_ktp_mempelai_pria',
        'file_ktp_mempelai_wanita',
        'file_ktp_saksi_1',
        'file_ktp_saksi_2',
        // File dokumen final hasil penerbitan Disdukcapil
        'file_akta_pernikahan',
        'file_kk_pasangan',
        'file_kk_ortu_pria',
        'file_kk_ortu_wanita',
        'dokumen_final_uploaded_at',
    ];

    protected $casts = [
        'tanggal_lahir_mempelai_pria' => 'date',
        'tanggal_lahir_mempelai_wanita' => 'date',
        'tanggal_lahir_ayah_pria' => 'date',
        'tanggal_lahir_ibu_pria' => 'date',
        'tanggal_lahir_saksi_1' => 'date',
        'tanggal_lahir_saksi_2' => 'date',
        'tanggal_perkawinan' => 'date',
        'dokumen_final_uploaded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public const STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN = 'MENUNGGU_KONFIRMASI_KEAGAMAAN';
    public const STATUS_DITOLAK_KEAGAMAAN = 'DITOLAK_KEAGAMAAN';
    public const STATUS_MENUNGGU_APPROVE_TANGGAL = 'MENUNGGU_APPROVE_TANGGAL';
    public const STATUS_TANGGAL_DITOLAK = 'TANGGAL_DITOLAK';
    public const STATUS_TANGGAL_DISETUJUI = 'TANGGAL_DISETUJUI';
    public const STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI = 'DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI';
    public const STATUS_DOKUMEN_PERLU_PERBAIKAN = 'DOKUMEN_PERLU_PERBAIKAN';
    public const STATUS_DOKUMEN_DIVERIFIKASI = 'DOKUMEN_DIVERIFIKASI';
    public const STATUS_SELESAI = 'SELESAI';

    public const STATUS_TO_LABEL = [
        self::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN => 'Menunggu Konfirmasi Keagamaan',
        self::STATUS_DITOLAK_KEAGAMAAN => 'Ditolak oleh Keagamaan',
        self::STATUS_MENUNGGU_APPROVE_TANGGAL => 'Menunggu Persetujuan Tanggal',
        self::STATUS_TANGGAL_DITOLAK => 'Tanggal Ditolak',
        self::STATUS_TANGGAL_DISETUJUI => 'Tanggal Disetujui',
        self::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi Dokumen',
        self::STATUS_DOKUMEN_PERLU_PERBAIKAN => 'Dokumen Perlu Perbaikan',
        self::STATUS_DOKUMEN_DIVERIFIKASI => 'Dokumen Diverifikasi',
        self::STATUS_SELESAI => 'Selesai',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->pernikahan_id)) {
                $model->pernikahan_id = (string) Str::uuid();
            }

            if (empty($model->nomor_antrian)) {
                $model->nomor_antrian = self::generateNomorAntrian();
            }
        });

        static::created(function (self $model): void {
            \App\Services\PernikahanLacakBerkasService::recordStatus($model);
        });

        static::updated(function (self $model): void {
            if ($model->isDirty('status') && $model->getOriginal('status') !== $model->status) {
                StatusPerkawinanHistory::create([
                    'pernikahan_id' => $model->pernikahan_id,
                    'status_sebelum' => $model->getOriginal('status'),
                    'status_setelah' => $model->status,
                    'catatan' => 'Status berubah',
                    'changed_by' => auth()->id(),
                ]);

                \App\Services\PernikahanLacakBerkasService::recordStatus($model);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(DokumenPernikahan::class, 'pernikahan_id', 'pernikahan_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(StatusPerkawinanHistory::class, 'pernikahan_id', 'pernikahan_id')
            ->orderBy('created_at', 'desc');
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeMenungguKonfirmasiKeagamaan(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN);
    }

    public function scopeMenungguApproveTanggal(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_MENUNGGU_APPROVE_TANGGAL);
    }

    public function scopeMenungguVerifikasiDokumen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI);
    }

    public function scopeByNik(Builder $query, string $nik): Builder
    {
        return $query->where('nik_pemohon', trim($nik))
            ->orWhere('nik_mempelai_pria', trim($nik))
            ->orWhere('nik_mempelai_wanita', trim($nik));
    }

    public static function generateNomorAntrian(): string
    {
        $prefix = 'PNK';
        $date = now()->format('Ymd');

        $lastAntrian = self::withTrashed()
            ->where('nomor_antrian', 'like', "{$prefix}-{$date}-%")
            ->orderBy('nomor_antrian', 'desc')
            ->first();

        if ($lastAntrian) {
            $lastNumber = (int) substr($lastAntrian->nomor_antrian, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf("%s-%s-%03d", $prefix, $date, $newNumber);
    }

    public function canUploadDocuments(): bool
    {
        if (!in_array($this->status, [
            self::STATUS_TANGGAL_DISETUJUI,
            self::STATUS_DOKUMEN_PERLU_PERBAIKAN,
        ])) {
            return false;
        }

        if (!$this->tanggal_perkawinan) {
            return false;
        }

        $tanggalObj = Carbon::parse($this->tanggal_perkawinan);
        $hariIni = Carbon::today();
        $selisihHari = $hariIni->diffInDays($tanggalObj, false);

        return $selisihHari >= 7;
    }

    public function getDeadlineUpload(): ?Carbon
    {
        if (!$this->tanggal_perkawinan) {
            return null;
        }

        return Carbon::parse($this->tanggal_perkawinan)->subDays(7)->endOfDay();
    }

    public function isDeadlinePassed(): bool
    {
        $deadline = $this->getDeadlineUpload();
        return $deadline && $deadline->isPast();
    }

    /** @var array<string, int> */
    public const STATUS_TO_STEP = [
        self::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN => 1,
        self::STATUS_DITOLAK_KEAGAMAAN => 1,
        self::STATUS_MENUNGGU_APPROVE_TANGGAL => 2,
        self::STATUS_TANGGAL_DITOLAK => 2,
        self::STATUS_TANGGAL_DISETUJUI => 3,
        self::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI => 3,
        self::STATUS_DOKUMEN_PERLU_PERBAIKAN => 3,
        self::STATUS_DOKUMEN_DIVERIFIKASI => 4,
        self::STATUS_SELESAI => 5,
    ];

    public static function stepFromStatus(?string $status): int
    {
        return self::STATUS_TO_STEP[$status] ?? 1;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN => 'Menunggu Konfirmasi Keagamaan',
            self::STATUS_DITOLAK_KEAGAMAAN => 'Ditolak oleh Keagamaan',
            self::STATUS_MENUNGGU_APPROVE_TANGGAL => 'Menunggu Persetujuan Tanggal',
            self::STATUS_TANGGAL_DITOLAK => 'Tanggal Ditolak',
            self::STATUS_TANGGAL_DISETUJUI => 'Tanggal Disetujui',
            self::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi Dokumen',
            self::STATUS_DOKUMEN_PERLU_PERBAIKAN => 'Dokumen Perlu Perbaikan',
            self::STATUS_DOKUMEN_DIVERIFIKASI => 'Dokumen Diverifikasi',
            self::STATUS_SELESAI => 'Selesai',
            default => 'Unknown',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN, self::STATUS_MENUNGGU_APPROVE_TANGGAL,
            self::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI => 'yellow',
            self::STATUS_DITOLAK_KEAGAMAAN, self::STATUS_TANGGAL_DITOLAK,
            self::STATUS_DOKUMEN_PERLU_PERBAIKAN => 'red',
            self::STATUS_TANGGAL_DISETUJUI, self::STATUS_DOKUMEN_DIVERIFIKASI,
            self::STATUS_SELESAI => 'green',
            default => 'gray',
        };
    }

    public function getStepAttribute(): int
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN, self::STATUS_DITOLAK_KEAGAMAAN => 1,
            self::STATUS_MENUNGGU_APPROVE_TANGGAL, self::STATUS_TANGGAL_DITOLAK => 2,
            self::STATUS_TANGGAL_DISETUJUI, self::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
            self::STATUS_DOKUMEN_PERLU_PERBAIKAN, self::STATUS_DOKUMEN_DIVERIFIKASI => 3,
            self::STATUS_SELESAI => 4,
            default => 1,
        };
    }

    /**
     * Cek apakah semua dokumen wajib sudah diupload
     */
    public function isDokumenLengkap(): bool
    {
        $dokumenWajib = [
            'surat_keterangan',
            'ktp_mempelai',
            'kartu_keluarga',
            'ktp_saksi'
        ];

        $uploadedDokumen = $this->dokumen()
            ->whereIn('jenis_dokumen', $dokumenWajib)
            ->where('status', '!=', DokumenPernikahan::STATUS_DITOLAK)
            ->pluck('jenis_dokumen')
            ->unique()
            ->toArray();

        // Cek apakah semua dokumen wajib sudah ada
        foreach ($dokumenWajib as $jenis) {
            if (!in_array($jenis, $uploadedDokumen)) {
                return false;
            }
        }

        return true;
    }
}
