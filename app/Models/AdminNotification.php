<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * AdminNotification — Model notifikasi untuk admin.
 */
class AdminNotification extends Model
{
    use HasFactory;

    protected $table = 'admin_notifications';

    protected $primaryKey = 'notification_id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'link',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const TYPE_LAYANAN_BARU = 'layanan_baru';
    public const TYPE_STATUS_UPDATE = 'status_update';
    public const TYPE_DOKUMEN_BARU = 'dokumen_baru';
    public const TYPE_INFO = 'info';

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function markAsRead(): bool
    {
        $this->is_read = true;
        $this->read_at = now();
        return $this->save();
    }

    public function getIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_LAYANAN_BARU => 'fas fa-file-alt text-blue-500',
            self::TYPE_STATUS_UPDATE => 'fas fa-sync-alt text-orange-500',
            self::TYPE_DOKUMEN_BARU => 'fas fa-paperclip text-green-500',
            self::TYPE_INFO => 'fas fa-info-circle text-gray-500',
            default => 'fas fa-bell text-gray-500',
        };
    }

    public function getBadgeColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_LAYANAN_BARU => 'bg-blue-500',
            self::TYPE_STATUS_UPDATE => 'bg-orange-500',
            self::TYPE_DOKUMEN_BARU => 'bg-green-500',
            self::TYPE_INFO => 'bg-gray-500',
            default => 'bg-gray-500',
        };
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}
