<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Merchant extends Model
{
    protected $fillable = [
        'vendor_id', 'code', 'client_reference', 'registration_hash', 'provider_merchant_id', 'provider_ref',
        'pipe', 'first_name', 'last_name', 'phone_masked', 'aadhaar_masked',
        'provider_status_code', 'onboarding_status', 'provider_status_description',
        'two_fa_at', 'sanitized_response',
    ];

    protected function casts(): array
    {
        return [
            'two_fa_at' => 'datetime',
            'sanitized_response' => 'array',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function aepsTransactions(): HasMany
    {
        return $this->hasMany(AepsTransaction::class);
    }

    public function commissionRules(): HasMany
    {
        return $this->hasMany(CommissionRule::class);
    }

    public function commissionEntries(): HasMany
    {
        return $this->hasMany(CommissionEntry::class);
    }
}
