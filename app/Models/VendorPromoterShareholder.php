<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPromoterShareholder extends Model
{
    protected $fillable = [
        'vendor_id',
        'full_name',
        'shareholding_percentage',
        'pan_card_no',
        'date_of_birth',
        'official_address',
    ];

    protected function casts(): array
    {
        return [
            'shareholding_percentage' => 'decimal:2',
            'date_of_birth' => 'date',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}