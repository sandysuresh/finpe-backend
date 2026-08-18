<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBusinessPlan extends Model
{
    protected $fillable = [
        'vendor_id',
        'month',
        'customer_registrations',
        'transactions',
        'total_volume',
    ];

    protected function casts(): array
    {
        return [
            'customer_registrations' => 'integer',
            'transactions' => 'integer',
            'total_volume' => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}