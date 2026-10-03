<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorApiAccess extends Model
{
    public const PAYOUT = 'payout';

    protected $table = 'vendor_api_access';

    protected $fillable = [
        'vendor_id',
        'api_code',
        'is_enabled',
        'assigned_at',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'assigned_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_by');
    }
}
