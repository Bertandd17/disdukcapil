<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatistikLayananBulanan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'statistik_layanan_bulanan';

    protected $primaryKey = 'statistik_layanan_bulanan_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    protected $fillable = [
        'statistik_layanan_bulanan_id',
        'tahun',
        'bulan',
        'total_antrian',
        'antrian_menunggu',
        'antrian_diproses',
        'antrian_selesai',
        'antrian_ditolak',
        'waktu_avg_penanganan_menit',
        'persentase_kepuasan',
        'is_auto_generated',
        'generated_at',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'total_antrian' => 'integer',
        'antrian_menunggu' => 'integer',
        'antrian_diproses' => 'integer',
        'antrian_selesai' => 'integer',
        'antrian_ditolak' => 'integer',
        'waktu_avg_penanganan_menit' => 'integer',
        'persentase_kepuasan' => 'decimal:2',
        'is_auto_generated' => 'boolean',
        'generated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Nama bulan dalam Bahasa Indonesia
     */
    public const BULAN_INDONESIA = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Accessor: Nama bulan dalam Bahasa Indonesia
     */
    public function getNamaBulanAttribute(): string
    {
        return self::BULAN_INDONESIA[$this->bulan] ?? 'Tidak Diketahui';
    }

    /**
     * Alias untuk nama_bulan
     */
    public function getBulanNamaAttribute(): string
    {
        return $this->nama_bulan;
    }

    /**
     * Accessor: Periode format lengkap
     */
    public function getPeriodeFormatAttribute(): string
    {
        return "{$this->nama_bulan} {$this->tahun}";
    }

    /**
     * Accessor: Total antrian dihitung otomatis
     */
    public function getTotalAntrianAttribute($value): int
    {
        if ($value !== null) {
            return $value;
        }
        
        return ($this->antrian_menunggu ?? 0) 
            + ($this->antrian_diproses ?? 0) 
            + ($this->antrian_selesai ?? 0) 
            + ($this->antrian_ditolak ?? 0);
    }

    /**
     * Accessor: Persentase antrian selesai
     */
    public function getPersentaseSelesaiAttribute(): float
    {
        if ($this->total_antrian == 0) return 0;
        return round(($this->antrian_selesai / $this->total_antrian) * 100, 2);
    }

    /**
     * Accessor: Persentase antrian menunggu
     */
    public function getPersentaseMenungguAttribute(): float
    {
        if ($this->total_antrian == 0) return 0;
        return round(($this->antrian_menunggu / $this->total_antrian) * 100, 2);
    }

    /**
     * Accessor: Persentase antrian diproses
     */
    public function getPersentaseDiprosesAttribute(): float
    {
        if ($this->total_antrian == 0) return 0;
        return round(($this->antrian_diproses / $this->total_antrian) * 100, 2);
    }

    /**
     * Accessor: Persentase antrian ditolak
     */
    public function getPersentaseDitolakAttribute(): float
    {
        if ($this->total_antrian == 0) return 0;
        return round(($this->antrian_ditolak / $this->total_antrian) * 100, 2);
    }

    /**
     * Accessor: Status badge color berdasarkan dominasi
     */
    public function getStatusBadgeAttribute(): string
    {
        if ($this->antrian_selesai > $this->antrian_menunggu && $this->antrian_selesai > $this->antrian_diproses) {
            return 'success';
        }
        
        if ($this->antrian_menunggu > $this->antrian_selesai) {
            return 'warning';
        }
        
        return 'info';
    }

    /**
     * Mutator: Set total_antrian saat disimpan
     */
    public function setTotalAntrianAttribute(): void
    {
        $this->attributes['total_antrian'] = 
            $this->antrian_menunggu 
            + $this->antrian_diproses 
            + $this->antrian_selesai 
            + $this->antrian_ditolak;
    }

    /**
     * Scope: Filter berdasarkan tahun
     */
    public function scopeTahun($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * Scope: Filter berdasarkan bulan
     */
    public function scopeBulan($query, int $bulan)
    {
        return $query->where('bulan', $bulan);
    }

    /**
     * Scope: Filter berdasarkan range periode
     */
    public function scopePeriode($query, int $tahun, int $bulanAwal, int $bulanAkhir)
    {
        return $query->where('tahun', $tahun)
            ->whereBetween('bulan', [$bulanAwal, $bulanAkhir]);
    }

    /**
     * Scope: Urutkan berdasarkan periode
     */
    public function scopeUrutPeriode($query)
    {
        return $query->orderBy('tahun', 'desc')->orderBy('bulan', 'desc');
    }

    /**
     * Scope: Hanya data auto-generated
     */
    public function scopeAutoGenerated($query)
    {
        return $query->where('is_auto_generated', true);
    }

    /**
     * Scope: Hanya data manual
     */
    public function scopeManual($query)
    {
        return $query->where('is_auto_generated', false);
    }

    /**
     * Boot method untuk auto-generate UUID
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->statistik_layanan_bulanan_id)) {
                $model->statistik_layanan_bulanan_id = (string) \Illuminate\Support\Str::uuid();
            }
        });

        static::saving(function (self $model): void {
            // Hitung total sebelum menyimpan
            $model->total_antrian = 
                ($model->antrian_menunggu ?? 0) 
                + ($model->antrian_diproses ?? 0) 
                + ($model->antrian_selesai ?? 0) 
                + ($model->antrian_ditolak ?? 0);
        });
    }
}
