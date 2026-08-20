<?php

namespace App\Models\Concerns;

use App\Support\UrlId;

trait HasEncryptedRouteKey
{
    public function getRouteKey(): string
    {
        return UrlId::encode($this->getKey());
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $id = UrlId::decode(is_string($value) ? $value : null);

        if ($id === null) {
            return null;
        }

        return $this->where($field ?? $this->getRouteKeyName(), $id)->first();
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        $id = UrlId::decode(is_string($value) ? $value : null);

        if ($id === null) {
            return null;
        }

        return parent::resolveChildRouteBinding($childType, $id, $field);
    }
}
