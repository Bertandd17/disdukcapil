<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LayananModel extends Model
{
    use HasFactory;

    protected $table = 'layanan';

    protected $primaryKey = 'layanan_id';

    public $timestamps = true;

    protected $fillable = [
        'layanan_id',
        'kode_layanan',
        'nama_layanan',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            if (empty($model->layanan_id)) {
                $model->layanan_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Relasi ke antrian online
     */
    public function antrian_online(): HasMany
    {
        return $this->hasMany(AntrianOnlineModel::class, 'layanan_id', 'layanan_id');
    }

    public static function findByIdentifier(?string $identifier): ?self
    {
        $identifier = trim((string) $identifier);

        if ($identifier === '') {
            return null;
        }

        return self::query()
            ->where('layanan_id', $identifier)
            ->orWhere('kode_layanan', $identifier)
            ->first();
    }
}
