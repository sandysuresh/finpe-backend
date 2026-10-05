<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Livewire\Admin\PayoutTransactions;
use App\Models\Transaction;
use App\Support\PlainPdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PayoutReceiptController extends Controller
{
    public function __invoke(string $reference): Response
    {
        $vendor = Auth::guard('vendor')->user();
        $txn = Transaction::query()
            ->where('vendor_id', $vendor->id)
            ->where('type', 'payout')
            ->where('reference', $reference)
            ->first();

        if (! $txn) {
            abort(404);
        }

        $charge = (float) $txn->payout_charge;
        $amount = (float) $txn->amount;
        $total = round($amount + $charge, 2);
        $bank = trim(($txn->bank_name ?: '').($txn->bank_name && $txn->beneficiary_bank_code ? ' · ' : '').($txn->beneficiary_bank_code ?: ''));
        if ($bank === '') {
            $bank = $txn->beneficiary_bank_code ?: '-';
        }

        return response(PlainPdf::payoutReceipt([
            'reference' => $txn->reference,
            'merchant_ref' => $txn->merchant_ref ?: '-',
            'provider_reference' => $txn->bank_reference ?: '-',
            'vendor' => $vendor->business_name ?: '-',
            'provider' => $txn->payout_provider ?: '-',
            'beneficiary_name' => $txn->beneficiary_name ?: '-',
            'account' => PayoutTransactions::maskAccountStatic($txn->account_number),
            'ifsc' => $txn->ifsc_code ?: '-',
            'bank' => $bank,
            'mobile' => PayoutTransactions::maskMobileStatic($txn->beneficiary_mobile),
            'location' => $txn->beneficiary_location ?: '-',
            'payment_mode' => strtoupper((string) $txn->service) ?: '-',
            'payment_purpose' => $txn->payment_purpose ?: '-',
            'amount' => 'INR '.number_format($amount, 2),
            'charge' => 'INR '.number_format($charge, 2),
            'total_debited' => 'INR '.number_format($total, 2),
            'status' => (string) $txn->status,
            'provider_message' => $txn->failure_reason ?: '-',
            'created_at' => $txn->created_at?->format('d M Y, h:i A') ?: '-',
            'updated_at' => $txn->updated_at?->format('d M Y, h:i A') ?: '-',
        ]), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="payout-receipt-'.$txn->reference.'.pdf"',
        ]);
    }
}
