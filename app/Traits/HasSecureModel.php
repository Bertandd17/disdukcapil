<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

trait HasSecureModel
{
    /**
     * Boot the secure model trait
     *
     * @return void
     */
    protected static function bootHasSecureModel()
    {
        // Log semua operasi delete
        static::deleting(function ($model) {
            Log::info('Model deleted', [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'user_id' => auth()->id(),
            ]);
        });

        // Log semua operasi update
        static::updating(function ($model) {
            Log::info('Model updated', [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'changes' => $model->getDirty(),
                'user_id' => auth()->id(),
            ]);
        });

        // Log semua operasi create
        static::creating(function ($model) {
            Log::info('Model creating', [
                'model' => get_class($model),
                'attributes' => $model->getAttributes(),
                'user_id' => auth()->id(),
            ]);
        });
    }

    /**
     * Secure scope untuk prevent mass assignment
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $fillable
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSecureFill(Builder $query, array $data)
    {
        $allowed = $this->getFillable();
        $filtered = array_intersect_key($data, array_flip($allowed));

        return $query->update($filtered);
    }

    /**
     * Secure update dengan mass assignment protection
     *
     * @param  array  $data
     * @param  array  $allowedFields
     * @return bool
     */
    public function secureUpdate(array $data, array $allowedFields = [])
    {
        $fieldsToFill = empty($allowedFields) ? $this->getFillable() : $allowedFields;
        $filteredData = array_intersect_key($data, array_flip($fieldsToFill));

        return $this->update($filteredData);
    }

    /**
     * Cek apakah field boleh di-mass assign
     *
     * @param  string  $field
     * @return bool
     */
    public function isFillable(string $field): bool
    {
        return in_array($field, $this->getFillable());
    }

    /**
     * Filter data hanya field yang fillable
     *
     * @param  array  $data
     * @return array
     */
    public function filterFillable(array $data): array
    {
        return array_intersect_key($data, array_flip($this->getFillable()));
    }

    /**
     * Override fill untuk logging
     *
     * @param  array  $attributes
     * @return $this
     */
    public function fill(array $attributes)
    {
        $filtered = $this->filterFillable($attributes);

        // Log jika ada field yang difilter
        $blockedFields = array_diff(array_keys($attributes), array_keys($filtered));
        if (!empty($blockedFields)) {
            Log::warning('Mass assignment attempt blocked', [
                'model' => get_class($this),
                'blocked_fields' => $blockedFields,
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
            ]);
        }

        return parent::fill($filtered);
    }
}
