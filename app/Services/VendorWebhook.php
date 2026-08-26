<?php

namespace App\Services;

use App\Models\Vendor;
use App\Models\WebhookLog;
use App\Support\OutboundUrl;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class VendorWebhook
{
    public function send(Vendor $vendor, string $event, array $payload): void
    {
        $url = $vendor->apiCredential?->webhook_url;

        if (! $url) {
            return;
        }

        try {
            OutboundUrl::assertSafe($url, false);
        } catch (InvalidArgumentException $e) {
            Log::notice('webhook_url_rejected', ['vendor_id' => $vendor->id, 'reason' => $e->getMessage()]);

            return;
        }

        $log = WebhookLog::create([
            'vendor_id' => $vendor->id,
            'event' => $event,
            'url' => $url,
            'payload' => $payload,
            'status' => 'pending',
            'attempts' => 1,
        ]);

        try {
            $response = Http::timeout(8)
                ->connectTimeout(5)
                ->withOptions(['allow_redirects' => false])
                ->asJson()
                ->post($url, [
                    'event' => $event,
                    'data' => $payload,
                ]);

            $log->update([
                'response_code' => $response->status(),
                'response_body' => mb_substr($response->body(), 0, 500),
                'status' => $response->successful() ? 'success' : 'failed',
            ]);
        } catch (Throwable $e) {
            Log::warning('webhook_delivery_failed', ['vendor_id' => $vendor->id]);
            $log->update([
                'status' => 'failed',
                'response_body' => 'Delivery failed',
            ]);
        }
    }
}
