<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lacak_Berkas_Model extends Model
{
    use HasFactory;

    protected $table = 'lacak_berkas';
    protected $primaryKey = 'lacak_berkas_id';
    public $timestamps = true;

    protected $fillable = [
        'antrian_online_id',
        'status',
        'tanggal',
        'keterangan',
        'alasan_penolakan',
        'detail_form',
        'file_berkas',
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The "type" of the auto-increment ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * Boot function - removed UUID generation as lacak_berkas_id uses auto_increment
     */
    protected static function boot()
    {
        parent::boot();
        // Auto_increment ID is handled by database
    }

    /**
     * Relasi ke antrian online
     */
    public function antrian_online(): BelongsTo
    {
        return $this->belongsTo(Antrian_Online_Model::class, 'antrian_online_id', 'antrian_online_id');
    }
    
    /**
     * Alias relasi untuk backward compatibility
     */
    public function AntrianOnline(): BelongsTo
    {
        return $this->belongsTo(Antrian_Online_Model::class, 'antrian_online_id', 'antrian_online_id');
    }

    /**
     * Alias relasi 'antrian' untuk digunakan di view
     */
    public function antrian(): BelongsTo
    {
        return $this->belongsTo(Antrian_Online_Model::class, 'antrian_online_id', 'antrian_online_id');
    }
}
