<?php

namespace App\Support;

use App\Models\Vendor;
use App\Models\VendorNotification;

class VendorNotify
{
    public static function kycDecision(Vendor $vendor, bool $approved, string $comment = ''): void
    {
        $note = trim($comment);

        VendorNotification::create([
            'vendor_id' => $vendor->id,
            'type' => 'kyc',
            'title' => $approved ? 'KYC approved' : 'KYC rejected',
            'body' => $approved
                ? 'Your KYC was approved.'.($note !== '' ? ' '.$note : '')
                : 'Your KYC was rejected.'.($note !== '' ? ' '.$note : ''),
            'action_url' => route('vendor.profile', [], false),
        ]);
    }

    public static function walletDecision(Vendor $vendor, bool $approved, string $reference, string $amount, string $note = ''): void
    {
        $formatted = '₹'.number_format((float) $amount, 2);
        $note = trim($note);

        VendorNotification::create([
            'vendor_id' => $vendor->id,
            'type' => 'wallet',
            'title' => $approved ? 'Wallet request approved' : 'Wallet request rejected',
            'body' => $approved
                ? "{$formatted} ({$reference}) was credited to your wallet.".($note !== '' ? ' '.$note : '')
                : "Your wallet request {$reference} for {$formatted} was rejected.".($note !== '' ? ' '.$note : ''),
            'action_url' => route('vendor.wallet', [], false).'?tab=requests',
        ]);
    }
}
