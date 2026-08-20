<?php

namespace App\Support;

use App\Models\AdminNotification;
use App\Models\Vendor;

class AdminNotify
{
    public static function kycSubmitted(Vendor $vendor, bool $resubmitted = false): void
    {
        AdminNotification::create([
            'type' => 'kyc',
            'title' => $resubmitted ? 'KYC resubmitted' : 'New KYC request',
            'body' => ($vendor->business_name ?: $vendor->vendor_code)
                . ($resubmitted
                    ? ' resubmitted KYC after rejection.'
                    : ' submitted KYC for review.'),
            'action_url' => route('admin.vendors.show', $vendor, false) . '?tab=kyc',
            'vendor_id' => $vendor->id,
        ]);
    }

    public static function walletTopup(Vendor $vendor, string $reference, string $amount, int $requestId): void
    {
        AdminNotification::create([
            'type' => 'wallet',
            'title' => 'Wallet top-up request',
            'body' => ($vendor->business_name ?: $vendor->vendor_code)
                . ' requested ₹' . number_format((float) $amount, 2)
                . " ({$reference}).",
            'action_url' => route('admin.wallet-requests', [], false)
                . '?request=' . UrlId::encode($requestId),
            'vendor_id' => $vendor->id,
        ]);
    }
}
