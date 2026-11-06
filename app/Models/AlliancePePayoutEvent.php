<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlliancePePayoutEvent extends Model
{
    protected $table = 'alliancepe_payout_events';

    protected $fillable = [
        'alliancepe_payout_id', 'status', 'wallet_state', 'utr_number', 'failure_reason', 'source',
    ];

    public function payout(): BelongsTo
    {
        return $this->belongsTo(AlliancePePayout::class, 'alliancepe_payout_id');
    }
}
