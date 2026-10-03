<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Livewire\Admin\AepsTransactions;
use App\Models\AepsTransaction;
use App\Services\Aeps\AepsPayloadSanitizer;
use App\Support\PlainPdf;
use Illuminate\Http\Response;

class AepsReceiptController extends Controller
{
    public function __invoke(string $reference): Response
    {
        $txn = AepsTransaction::query()
            ->with(['vendor:id,business_name', 'merchant:id,code,first_name,last_name'])
            ->where('reference', $reference)
            ->first();

        if (! $txn) {
            abort(404);
        }

        $merchant = trim(($txn->merchant->code ?? '').' '.trim(($txn->merchant->first_name ?? '').' '.($txn->merchant->last_name ?? '')));

        return response(PlainPdf::receipt([
            'reference' => $txn->reference,
            'provider_reference' => $txn->provider_txn_ref ?: '-',
            'rrn' => $txn->rrn ?: '-',
            'vendor' => $txn->vendor->business_name ?? '-',
            'merchant' => $merchant !== '' ? $merchant : '-',
            'service' => AepsTransactions::serviceLabel($txn->service),
            'amount' => 'INR '.number_format((float) $txn->amount, 2),
            'status' => str_replace('_', ' ', (string) $txn->status),
            'provider_status' => $txn->provider_status_code ?: '-',
            'provider_message' => $txn->provider_status_description ?: '-',
            'datetime' => $txn->created_at?->format('d M Y, h:i A') ?: '-',
            'aadhaar' => AepsPayloadSanitizer::displayAadhaar($txn->aadhaar_masked),
        ]), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="aeps-receipt-'.$txn->reference.'.pdf"',
        ]);
    }
}
