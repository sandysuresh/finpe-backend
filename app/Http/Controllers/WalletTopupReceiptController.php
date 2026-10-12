<?php

namespace App\Http\Controllers;

use App\Models\WalletTopupRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WalletTopupReceiptController extends Controller
{
    public function admin(int $id): StreamedResponse
    {
        $request = WalletTopupRequest::query()->findOrFail($id);

        return $this->download($request);
    }

    public function vendor(int $id): StreamedResponse
    {
        $request = WalletTopupRequest::query()
            ->where('vendor_id', Auth::guard('vendor')->id())
            ->findOrFail($id);

        return $this->download($request);
    }

    private function download(WalletTopupRequest $request): StreamedResponse
    {
        $path = (string) $request->receipt_path;
        abort_unless(
            $path !== '' && ! str_contains($path, '..') && str_starts_with($path, 'wallet-receipts/'),
            404
        );
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, 'add-money-receipt-'.$request->reference);
    }
}
