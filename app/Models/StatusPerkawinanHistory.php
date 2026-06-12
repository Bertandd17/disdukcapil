<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StatusPerkawinanHistory — model riwayat perubahan status layanan pernikahan.
 *
 * Mencatat setiap perubahan status beserta informasi
 * siapa yang mengubah dan catatan terkait perubahan.
 */
class StatusPerkawinanHistory extends Model
{
    use HasFactory;

    protected $table = 'status_perkawinan_history';

    protected $fillable = [
        'pernikahan_id',
        'status_sebelum',
        'status_setelah',
        'catatan',
        'changed_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pernikahan(): BelongsTo
    {
        return $this->belongsTo(LayananPernikahan::class, 'pernikahan_id', 'pernikahan_id');
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by', 'id');
    }

    public function getStatusSebelumLabelAttribute(): string
    {
        return LayananPernikahan::STATUS_TO_LABEL[$this->status_sebelum] ?? $this->status_sebelum;
    }

    public function getStatusSetelahLabelAttribute(): string
    {
        return LayananPernikahan::STATUS_TO_LABEL[$this->status_setelah] ?? $this->status_setelah;
    }
}
