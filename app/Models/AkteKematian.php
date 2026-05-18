<?php

namespace App\Models;

use App\Traits\EncryptsSensitiveData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AkteKematian extends Model
{
    use SoftDeletes, EncryptsSensitiveData;

    protected $table = 'akte_kematian';

    protected $fillable = [
        'uuid',
        'layanan_id',
        'antrian_online_id',
        'nomor_antrian',
        
        // Data Pemohon
        'nik_pemohon',
        'nomor_kk_pemohon',
        'nama_pemohon',
        'alamat_pemohon',
        'hubungan_pemohon',
        
        // Data Berkas
        'ktp_pemohon',
        'kartu_keluarga_pemohon',
        'formulir_f201',
        'surat_keterangan_kematian',
        'ktp_almarhum',
        'ktp_saksi1',
        'ktp_saksi2',
        
        // Status
        'status',
        'alasan_penolakan',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Daftar sensitive fields yang akan di-encrypt
     *
     * @return array
     */
    public function getSensitiveFields(): array
    {
        // Gabungan dari local dan remote
        return [
            'nik_pemohon',
            'nomor_kk_pemohon',
            'nik_almarhum',
            'nik_pelapor',
            'surat_keterangan_kematian',
            'ktp_almarhum',
            'kartu_keluarga',
        ];
    }

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        
        // UUID generation dari remote
        self::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        // Encrypt sensitive data dari local
        static::bootEncryptsSensitiveData();
    }

    /**
     * Relasi dengan layanan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function layanan()
    {
        return $this->belongsTo(Layanan_Model::class, 'layanan_id', 'layanan_id');
    }

    /**
     * Relasi ke Antrian Online
     */
    public function antrian_online()
    {
        return $this->belongsTo(Antrian_Online_Model::class, 'antrian_online_id', 'antrian_online_id');
    }
}
