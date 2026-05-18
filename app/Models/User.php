<?php

namespace App\Models;

use App\Traits\EncryptsSensitiveData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use EncryptsSensitiveData, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'id',
        'name',
        'username',
        'password',
        'security_question_id',
        'security_question_answer',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'security_question_answer',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function getSensitiveFields(): array
    {
        return [
            'security_question_answer',
        ];
    }

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        static::bootEncryptsSensitiveData();
    }

    public function username(): string
    {
        return 'username';
    }

    public function securityQuestion()
    {
        return $this->belongsTo(SecurityQuestion::class);
    }

    public function detail_keagamaan()
    {
        return $this->hasOne(Keagamaan_Model::class, 'user_id', 'id');
    }
}