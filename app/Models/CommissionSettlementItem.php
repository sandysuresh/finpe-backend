<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionSettlementItem extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'commission_settlement_id', 'commission_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(CommissionSettlement::class, 'commission_settlement_id');
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(CommissionEntry::class, 'commission_entry_id');
    }
}
