<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisKeagamaanModel extends Model
{
    use HasFactory;

    protected $table = 'jenis_keagamaan';
    protected $primaryKey = 'jenis_keagamaan_id';
    protected $fillable = [
        'nama_jenis_keagamaan',
    ];

    public $timestamps = false;
}
