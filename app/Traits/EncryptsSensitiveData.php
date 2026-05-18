<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * Trait untuk handle encryption dan decryption field sensitive
 */
trait EncryptsSensitiveData
{
    /**
     * Boot trait untuk menambahkan event listeners
     */
    protected static function bootEncryptsSensitiveData()
    {
        static::saving(function ($model) {
            $model->encryptSensitiveFields();
        });

        static::retrieved(function ($model) {
            $model->decryptSensitiveFields();
        });
    }

    /**
     * Daftar field yang akan di-encrypt
     * Override di model untuk menyesuaikan
     */
    public function getSensitiveFields(): array
    {
        return [];
    }

    /**
     * Encrypt semua sensitive fields sebelum disimpan
     */
    protected function encryptSensitiveFields(): void
    {
        $fields = $this->getSensitiveFields();

        foreach ($fields as $field) {
            if (isset($this->attributes[$field]) && ! empty($this->attributes[$field])) {
                try {
                    $value = $this->attributes[$field];

                    if ($this->isAlreadyEncrypted($value)) {
                        continue;
                    }

                    $this->attributes[$field] = Crypt::encryptString($value);
                } catch (\Exception $e) {
                    Log::error('Failed to encrypt sensitive field', [
                        'model' => get_class($this),
                        'field' => $field,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Decrypt semua sensitive fields setelah diambil dari database
     */
    protected function decryptSensitiveFields(): void
    {
        $fields = $this->getSensitiveFields();

        foreach ($fields as $field) {
            if (isset($this->attributes[$field]) && ! empty($this->attributes[$field])) {
                try {
                    $value = $this->attributes[$field];

                    if ($this->isAlreadyEncrypted($value)) {
                        $decrypted = Crypt::decryptString($value);
                        $this->attributes[$field] = $decrypted;
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to decrypt sensitive field', [
                        'model' => get_class($this),
                        'field' => $field,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Cek apakah value sudah di-encrypt
     */
    protected function isAlreadyEncrypted(string $value): bool
    {
        return strlen($value) > 50 || preg_match('/[^a-zA-Z0-9\-._@]/', $value);
    }
}
