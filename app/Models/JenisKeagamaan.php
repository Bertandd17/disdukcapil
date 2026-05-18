<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisKeagamaan extends Model
{
    use HasFactory;

    protected $table = 'jenis_keagamaan';

    protected $fillable = [
        'nama_jenis_keagamaan',
    ];

    /**
     * Relasi ke users yang memiliki jenis keagamaan ini
     */
    public function users()
    {
        return $this->hasMany(User::class, 'jenis_keagamaan_id');
    }
}
