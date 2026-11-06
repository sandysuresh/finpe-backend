<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlliancePeWebhookEvent extends Model
{
    protected $table = 'alliancepe_webhook_events';

    protected $fillable = [
        'webhook_id', 'event', 'provider_payout_id', 'merchant_order_id',
    ];
}
