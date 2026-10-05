<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ApiCredential extends Model
{
    protected $fillable = [
        'vendor_id', 'api_key', 'secret_key', 'webhook_url',
        'ip_whitelist', 'is_active', 'last_used_at',
    ];

    protected function casts(): array
    {
        return ['ip_whitelist' => 'array', 'is_active' => 'boolean', 'last_used_at' => 'datetime'];
    }

    protected $hidden = ['secret_key'];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function hmacSecret(): string
    {
        $stored = (string) ($this->attributes['secret_key'] ?? '');
        if (! str_starts_with($stored, 'eyJpdiI6')) {
            return $stored;
        }

        try {
            return Crypt::decryptString($stored);
        } catch (DecryptException) {
            return '';
        }
    }

    /**
     * Returns the new plaintext secret once. The stored value is encrypted.
     */
    public function rotateSecret(): string
    {
        $plain = 'sk_'.Str::random(48);
        $this->secret_key = Crypt::encryptString($plain);
        $this->save();

        return $plain;
    }

    /**
     * Returns the new plaintext secret once. The stored value is encrypted.
     */
    public static function issueFor(Vendor $vendor): string
    {
        $credential = static::query()->where('vendor_id', $vendor->id)->orderBy('id')->first() ?? new static([
            'vendor_id' => $vendor->id,
            'is_active' => true,
        ]);

        if (! $credential->exists) {
            $credential->api_key = 'pk_'.Str::random(32);
        }

        return $credential->rotateSecret();
    }

    public static function providerKeyPrefix(string $code): string
    {
        return 'pkp_'.strtolower(trim($code)).'_';
    }

    public static function forVendorProvider(Vendor $vendor, string $code): ?self
    {
        $code = strtolower(trim($code));
        if ($code === '') {
            return null;
        }

        $scoped = static::query()
            ->where('vendor_id', $vendor->id)
            ->where('api_key', 'like', static::providerKeyPrefix($code).'%')
            ->orderBy('id')
            ->first();
        if ($scoped) {
            return $scoped;
        }

        if ($code !== strtolower((string) config('payout.provider', ''))) {
            return null;
        }

        return static::query()
            ->where('vendor_id', $vendor->id)
            ->where('api_key', 'not like', 'pkp_%')
            ->orderBy('id')
            ->first();
    }

    /**
     * Creates a credential for one payout provider. An existing provider credential is not rotated or replaced.
     */
    public static function issueForProvider(Vendor $vendor, string $code): ?string
    {
        $code = strtolower(trim($code));
        if ($code === '' || static::forVendorProvider($vendor, $code)) {
            return null;
        }

        $legacy = static::query()->where('vendor_id', $vendor->id)->orderBy('id')->first();
        if (! $legacy && $code === strtolower((string) config('payout.provider', ''))) {
            return static::issueFor($vendor);
        }

        $credential = new static([
            'vendor_id' => $vendor->id,
            'api_key' => static::providerKeyPrefix($code).Str::random(32),
            'is_active' => true,
            'webhook_url' => $legacy?->webhook_url,
            'ip_whitelist' => $legacy?->ip_whitelist,
        ]);

        return $credential->rotateSecret();
    }
}
