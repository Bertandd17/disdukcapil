<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PisahKK extends Model
{
    protected $table = 'pisah_kk';
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
        'fotokopi_buku_nikah',
        'kk_lama',
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
