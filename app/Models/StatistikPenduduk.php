<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatistikPenduduk extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'statistik_penduduk';

    protected $primaryKey = 'statistik_penduduk_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    protected $fillable = [
        'statistik_penduduk_id',
        'kecamatan_id',
        'tahun',
        'total_penduduk',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'total_penduduk' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relasi: Statistik penduduk belongTo Kecamatan
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id', 'kecamatan_id');
    }

    /**
     * Scope: Filter berdasarkan tahun
     */
    public function scopeTahun($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * Scope: Filter berdasarkan tahun range
     */
    public function scopeRangeTahun($query, int $tahunAwal, int $tahunAkhir)
    {
        return $query->whereBetween('tahun', [$tahunAwal, $tahunAkhir]);
    }

    /**
     * Scope: Urutkan berdasarkan tahun terbaru
     */
    public function scopeTerbaru($query)
    {
        return $query->orderBy('tahun', 'desc');
    }

    /**
     * Scope: Cari berdasarkan nama kecamatan
     */
    public function scopeCariKecamatan($query, string $search)
    {
        return $query->whereHas('kecamatan', function ($q) use ($search) {
            $q->where('nama_kecamatan', 'like', "%{$search}%");
        });
    }

    /**
     * Accessor: Format total penduduk dengan separator
     */
    public function getTotalPendudukFormattedAttribute(): string
    {
        return number_format($this->total_penduduk, 0, ',', '.');
    }

    /**
     * Boot method untuk auto-generate UUID
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->statistik_penduduk_id)) {
                $model->statistik_penduduk_id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
