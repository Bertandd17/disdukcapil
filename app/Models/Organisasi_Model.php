<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Organisasi_Model extends Model
{
    use HasFactory;

    protected $table = 'organisasi';
    protected $primaryKey = 'id';
    protected $fillable = ['kode_posisi', 'nama_jabatan', 'parent_id', 'level', 'nama_pejabat', 'eselon', 'urutan'];

    public $incrementing = true;
    protected $keyType = 'int';

    // Level options
    const LEVEL_PIMPINAN_UTAMA = 'pimpinan_utama';
    const LEVEL_BIDANG = 'bidang';
    const LEVEL_SUB_BAGIAN = 'sub_bagian';
    const LEVEL_KOORDINATOR = 'koordinator';
    const LEVEL_KELOMPOK_FUNGSIONAL = 'kelompok_fungsional';

    public static function getLevels(): array
    {
        return [
            self::LEVEL_PIMPINAN_UTAMA => 'Pimpinan Utama',
            self::LEVEL_BIDANG => 'Bidang',
            self::LEVEL_SUB_BAGIAN => 'Sub Bagian',
            self::LEVEL_KOORDINATOR => 'Koordinator',
            self::LEVEL_KELOMPOK_FUNGSIONAL => 'Kelompok Fungsional',
        ];
    }

    public function getLevelLabelAttribute(): string
    {
        return self::getLevels()[$this->level] ?? 'Unknown';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Organisasi_Model::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Organisasi_Model::class, 'parent_id')->orderBy('urutan');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->orderBy('urutan');
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level)->orderBy('urutan');
    }

    public function scopeByLevelLike($query, $levelPattern)
    {
        return $query->where('level', 'like', $levelPattern)->orderBy('urutan');
    }
}
