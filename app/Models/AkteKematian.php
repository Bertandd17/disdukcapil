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
        'jenis_layanan',
        'alasan_penolakan',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    // Hapus $incrementing = false dan $keyType = 'string'
    // karena id adalah auto increment integer biasa

    public function getSensitiveFields(): array
    {
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

    protected static function boot()
    {
        parent::boot();
        
        self::creating(function ($model) {
            // Hapus bagian $model->id = Str::uuid()
            // biarkan auto increment yang handle
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        static::bootEncryptsSensitiveData();
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan_Model::class, 'layanan_id', 'layanan_id');
    }
}