<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionRule extends Model
{
    protected $fillable = [
        'name', 'vendor_id', 'merchant_id', 'service', 'type',
        'calc_type', 'value', 'status', 'effective_from', 'effective_to',
        'priority', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'effective_from' => 'datetime',
            'effective_to' => 'datetime',
            'priority' => 'integer',
        ];
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
