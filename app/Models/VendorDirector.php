<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorDirector extends Model
{
    protected $fillable = [
        'vendor_id',
        'name',
        'designation',
        'pan_card_no',
        'date_of_birth',
        'official_address',
        'profile_past_experience',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}