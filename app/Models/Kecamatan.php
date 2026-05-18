<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Kecamatan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kecamatan';

    protected $primaryKey = 'kecamatan_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    protected $fillable = [
        'kecamatan_id',
        'kode_kecamatan',
        'nama_kecamatan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi: Satu kecamatan memiliki banyak data statistik penduduk
     */
    public function statistikPenduduk(): HasMany
    {
        return $this->hasMany(StatistikPenduduk::class, 'kecamatan_id', 'kecamatan_id');
    }

    /**
     * Scope: Urutkan berdasarkan nama kecamatan
     */
    public function scopeUrutkanNama($query)
    {
        return $query->orderBy('nama_kecamatan', 'asc');
    }

    /**
     * Scope: Cari berdasarkan kode atau nama
     */
    public function scopeCari($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('kode_kecamatan', 'like', "%{$search}%")
              ->orWhere('nama_kecamatan', 'like', "%{$search}%");
        });
    }
}
