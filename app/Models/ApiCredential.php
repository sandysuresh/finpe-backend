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
        $credential = static::query()->where('vendor_id', $vendor->id)->first() ?? new static([
            'vendor_id' => $vendor->id,
            'is_active' => true,
        ]);

        if (! $credential->exists) {
            $credential->api_key = 'pk_'.Str::random(32);
        }

        return $credential->rotateSecret();
    }
}
