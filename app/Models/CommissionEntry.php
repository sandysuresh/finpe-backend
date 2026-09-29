<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommissionEntry extends Model
{
    public const UPDATED_AT = null;

    public const STATUS_RECORDED = 'recorded';

    protected $fillable = [
        'commission_rule_id', 'vendor_id', 'vendor_name_snapshot',
        'merchant_id', 'merchant_code_snapshot', 'service_snapshot', 'type_snapshot',
        'source_type', 'source_id', 'source_reference', 'source_status_snapshot',
        'base_amount', 'calc_type', 'rate_value', 'commission_amount', 'status',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:2',
            'rate_value' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class, 'commission_rule_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function sourceTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'source_id');
    }

    public function settlementItem(): HasOne
    {
        return $this->hasOne(CommissionSettlementItem::class);
    }
}
