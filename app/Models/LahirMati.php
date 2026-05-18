<?php

namespace App\Models;

use App\Traits\EncryptsSensitiveData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LahirMati extends Model
{
    use SoftDeletes, EncryptsSensitiveData;

    protected $table = 'lahir_mati';

    protected $fillable = [
        'uuid',
        'layanan_id',
        'antrian_online_id',
        'nomor_antrian',
        
        // Data Pemohon (Sesuai Konsep Baru)
        'nik_pemohon',
        'nomor_kk_pemohon',
        'nama_pemohon',
        'alamat_pemohon',
        'hubungan_pemohon',
        
        // Data Berkas
        'ktp_pemohon',
        'kartu_keluarga_pemohon',
        'ktp_saksi1',
        'ktp_saksi2',
        'formulir_f201',
        'surat_keterangan_lahir_mati',
        
        'keterangan',
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
     * Daftar sensitive fields yang akan di-encrypt (gabungan dari local dan remote)
     *
     * @return array
     */
    public function getSensitiveFields(): array
    {
        return [
            'nik_pemohon',
            'nomor_kk_pemohon',
            'nik_ayah',
            'nik_ibu',
            'surat_keterangan_lahir_mati',
            'ktp_ayah',
            'ktp_ibu',
        ];
    }

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        
        self::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

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
     * Relasi dengan antrian online
     */
    public function antrian_online()
    {
        return $this->belongsTo(Antrian_Online_Model::class, 'antrian_online_id', 'antrian_online_id');
    }
}
