<?php

namespace App\Models;

use App\Services\Aeps\AepsWalletRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AepsTransaction extends Model
{
    protected $fillable = [
        'vendor_id', 'merchant_id', 'reference', 'client_reference', 'request_hash',
        'service', 'amount', 'status', 'provider_status_code', 'provider_txn_ref',
        'rrn', 'npci_code', 'npci_message', 'provider_merchant_status',
        'provider_status_description', 'provider_available_balance', 'provider_transaction_list',
        'aadhaar_masked', 'bank_iin', 'wallet_effect', 'charge_effect', 'failure_reason',
        'sanitized_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'sanitized_response' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AepsTransaction $txn) {
            $txn->wallet_effect = AepsWalletRule::PENDING;
            $txn->charge_effect = AepsWalletRule::PENDING;
        });
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
