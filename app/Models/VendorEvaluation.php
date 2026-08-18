<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorEvaluation extends Model
{
    protected $fillable = [
        'vendor_id',
        'ca_name',
        'ca_constitution',
        'ca_incorporation_date',
        'networth',
        'credit_rating',
        'dealing_with_bank_since',
        'contract_expiry_date',
        'engagement_scope',
        'open_risk_issues',
        'documentation_status',
        'conflict_of_interest',
        'terminated_or_penalties',
        'rbi_defaulter',
        'recommendations',
    ];

    protected function casts(): array
    {
        return [
            'ca_incorporation_date' => 'date',
            'contract_expiry_date' => 'date',
            'networth' => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}