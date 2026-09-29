<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionSettlement extends Model
{
    protected $fillable = [
        'reference', 'vendor_id', 'period_start', 'period_end', 'total_amount',
        'status', 'created_by', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at',
        'wallet_ledger_id',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'total_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CommissionSettlementItem::class);
    }
}
