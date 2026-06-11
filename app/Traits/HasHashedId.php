<?php

namespace App\Traits;

use Hashids\Hashids;

trait HasHashedId
{
    /**
     * Get the hashed ID attribute.
     *
     * @return string
     */
    public function getHashedIdAttribute()
    {
        $hashids = new Hashids(env('APP_KEY'), 10);
        return $hashids->encode($this->id);
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // If the value is strictly numeric, it might be a raw ID (fallback for old links)
        if (is_numeric($value)) {
            return parent::resolveRouteBinding($value, $field);
        }

        $hashids = new Hashids(env('APP_KEY'), 10);
        $decoded = $hashids->decode($value);

        if (empty($decoded)) {
            return parent::resolveRouteBinding($value, $field);
        }

        return $this->where($field ?? $this->getRouteKeyName(), $decoded[0])->firstOrFail();
    }
}
