<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBusinessDetail extends Model
{
    protected $fillable = [
        'vendor_id',
        'entity_type',
        'registered_with',
        'registration_number',
        'pan_tin',
        'rbi_regulated',
        'incorporation_year',
        'merchant_acquiring_years',
        'corporate_office_address',
        'additional_licenses',
    ];

    protected function casts(): array
    {
        return [
            'rbi_regulated' => 'boolean',
            'incorporation_year' => 'integer',
            'merchant_acquiring_years' => 'integer',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}