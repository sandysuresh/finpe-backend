<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorTeamItDetail extends Model
{
    protected $fillable = [
        'vendor_id',
        'total_employees',
        'technology_employees',
        'sales_employees',
        'support_employees',
        'admin_finance_hr_employees',
        'it_system_overview',
        'processing_systems',
        'applications',
        'database_system',
        'switch_system',
        'terminals',
        'fraud_risk_management',
        'merchant_agent_management',
        'merchant_agent_portal',
        'additional_systems',
    ];

    protected function casts(): array
    {
        return [
            'total_employees' => 'integer',
            'technology_employees' => 'integer',
            'sales_employees' => 'integer',
            'support_employees' => 'integer',
            'admin_finance_hr_employees' => 'integer',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}