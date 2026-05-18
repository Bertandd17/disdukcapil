<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KartuKeluarga extends Model
{
    protected $table = 'ganti_data_kk';
    protected $primaryKey = 'uuid';      
    public $keyType = 'string';          
    public $incrementing = false;
    protected $fillable =[
        'uuid',
        'layanan_id',
        'nomor_antrian',
        'nama_pemohon',
        'nik_pemohon',
        'nomor_kk_pemohon',
        'alamat_pemohon',
        'formulir_f102',
        'ktp_pemohon',
        'kk_pemohon',
        'formulir_f106',
        'surat_keterangan_perubahan',
        'pernyataan_pindah_kk',
        'foto_wajah',
        'alasan_penolakan',
        'status',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = Str::uuid();
            }
        });
    }
}
