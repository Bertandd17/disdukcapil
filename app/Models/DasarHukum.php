<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DasarHukum extends Model
{
    protected $table = 'dasar_hukum';
    protected $fillable = [
        'uuid',
        'file',
        'nama',
        'deskripsi_singkat'
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
