<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organisasi extends Model
{
    protected $table = 'organisasi';

    protected $fillable = [
        'kode_posisi', 
        'nama_jabatan', 
        'nama_pejabat', 
        'eselon', 
        'urutan'
    ];
}
