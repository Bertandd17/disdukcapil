<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AkteLahir extends Model
{
    protected $table = 'aktelahirs';
    protected $fillable = [
    'uuid',
    'layanan_id',
    'nomor_antrian',
    'nama_pemohon',
    'nik_pemohon',
    'nomor_kk_pemohon',
    'alamat',
    'formulir_f201',
    'ktp_pemohon',
    'ktp_saksi1',
    'ktp_saksi2',
    'kk_pemohon',
    'file_surat_lahir',
    'file_buku_nikah',
    'file_sptjm_kelahiran',
    'file_sptjm_pasutri',
    'file_berita_acara_polisi',
    'foto_wajah',
    'alasan_penolakan',
    'status',
    'deleted_at',
    'created_at',
    'update_at'
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
