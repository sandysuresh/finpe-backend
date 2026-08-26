<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    protected $fillable = [
        'name', 'code', 'driver', 'environment', 'base_url',
        'username', 'password', 'api_key', 'api_secret',
        'services', 'is_active', 'is_default',
        'last_tested_at', 'last_test_status', 'last_test_message',
    ];

    protected $hidden = [
        'password', 'api_secret',
    ];

    protected function casts(): array
    {
        return [
            'username' => 'encrypted',
            'password' => 'encrypted',
            'api_key' => 'encrypted',
            'api_secret' => 'encrypted',
            'services' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'last_tested_at' => 'datetime',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'vendor_banks')
            ->withPivot('is_enabled')
            ->withTimestamps();
    }

    public function apiEndpoints(): HasMany
    {
        return $this->hasMany(BankApiEndpoint::class)->orderBy('sort_order')->orderBy('id');
    }

    public static function activeDefault(): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();
    }

    public function supports(string $service): bool
    {
        $services = $this->services ?: ['imps', 'neft', 'rtgs'];

        return in_array(strtolower($service), $services, true);
    }
}
