<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $fillable = [
        'vendor_id', 'bank_id', 'reference', 'bank_reference', 'amount', 'type',
        'channel', 'status', 'beneficiary_name', 'account_number', 'ifsc_code',
        'bank_name', 'remarks', 'service', 'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function commissionEntry(): HasOne
    {
        return $this->hasOne(CommissionEntry::class, 'source_id')
            ->where('commission_entries.source_type', 'transactions');
    }
}