<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Penghargaan extends Model
{
    protected $table = 'penghargaan';
    protected $fillable = [
        'uuid',
        'nama',
        'instansi',          
        'deskripsi_singkat',
        'tingkat',           
        'tahun',             
        'lokasi',            
        'file',
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
