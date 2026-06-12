<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Keagamaan_Model extends Model
{
    use HasFactory;

    protected $table = 'keagamaan';
    protected $primaryKey = 'keagamaan_id';
    protected $fillable = [
        'user_id',
        'jenis_keagamaan_id',
        'alamat',
        'status',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            if (empty($model->keagamaan_id)) {
                $model->keagamaan_id = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function jenis_keagamaan(): BelongsTo
    {
        return $this->belongsTo(Jenis_Keagamaan_Model::class, 'jenis_keagamaan_id', 'jenis_keagamaan_id');
    }
}
