<?php

namespace App\Support;

use App\Models\Vendor;
use Carbon\Carbon;

/**
 * Read-only payout row. This is not a Transaction and is never saved.
 */
class VisiblePayoutRow
{
    public function __construct(
        public int $id,
        public string $source,
        public string $reference,
        public ?string $merchant_ref,
        public ?int $vendor_id,
        public ?Vendor $vendor,
        public string $payout_provider,
        public string $type,
        public ?string $service,
        public float $amount,
        public float $payout_charge,
        public float $commission_amount,
        public float $total_debited,
        public ?string $beneficiary_name,
        public ?string $account_number,
        public ?string $ifsc_code,
        public ?string $bank_name,
        public ?string $beneficiary_bank_code,
        public ?string $beneficiary_mobile,
        public ?string $beneficiary_location,
        public ?string $payment_purpose,
        public ?string $bank_reference,
        public ?string $rrn,
        public ?string $status,
        public ?string $failure_reason,
        public ?object $commissionEntry,
        public ?float $recorded_commission,
        public ?Carbon $created_at,
        public ?Carbon $updated_at,
    ) {}
}
