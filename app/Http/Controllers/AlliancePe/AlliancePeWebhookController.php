<?php

namespace App\Http\Controllers\AlliancePe;

use App\Http\Controllers\Controller;
use App\Models\AlliancePePayout;
use App\Models\AlliancePeWebhookEvent;
use App\Services\AlliancePe\AlliancePeClient;
use App\Services\AlliancePe\AlliancePePayoutService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlliancePeWebhookController extends Controller
{
    /** @var list<string> */
    private const STATUSES = ['pending', 'processing', 'completed', 'failed', 'cancelled', 'rejected', 'on_hold'];

    public function __invoke(Request $request, AlliancePeClient $client, AlliancePePayoutService $payouts): JsonResponse
    {
        $secret = config('alliancepe.'.$client->environment().'.webhook_secret');
        $raw = $request->getContent();
        $signature = strtolower(trim((string) $request->header('X-Webhook-Signature', '')));
        $signature = str_starts_with($signature, 'sha256=') ? substr($signature, 7) : $signature;
        if (! is_string($secret) || $secret === '' || $signature === '') {
            return response()->json(['success' => false, 'message' => 'Invalid webhook signature.'], 401);
        }

        $expected = hash_hmac('sha256', $raw, $secret);
        if (! hash_equals($expected, $signature)) {
            return response()->json(['success' => false, 'message' => 'Invalid webhook signature.'], 401);
        }

        $payload = json_decode($raw, true);
        if (! is_array($payload)) {
            return response()->json(['success' => false, 'message' => 'Invalid webhook.'], 400);
        }

        $event = (string) ($payload['event'] ?? $request->header('X-Webhook-Event', ''));
        $webhookId = (string) ($payload['webhook_id'] ?? $request->header('X-Webhook-ID', ''));
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        if ($webhookId === '' || ! str_starts_with($event, 'payout.')) {
            return response()->json(['success' => false, 'message' => 'Invalid webhook.'], 400);
        }

        $status = strtolower((string) ($data['status'] ?? substr($event, strlen('payout.'))));
        if (! in_array($status, self::STATUSES, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid webhook.'], 400);
        }
        $data['status'] = $status;

        $merchantOrderId = is_scalar($data['merchantOrderId'] ?? null) ? trim((string) $data['merchantOrderId']) : '';
        $providerId = '';
        foreach (['payoutId', 'providerPayoutId', 'id'] as $key) {
            if (is_scalar($data[$key] ?? null) && trim((string) $data[$key]) !== '') {
                $providerId = trim((string) $data[$key]);
                break;
            }
        }

        if ($merchantOrderId === '' && $providerId === '') {
            return response()->json(['success' => false, 'message' => 'Unknown payout reference.'], 404);
        }

        try {
            $result = DB::transaction(function () use ($payouts, $data, $event, $webhookId, $merchantOrderId, $providerId) {
                if (AlliancePeWebhookEvent::query()->where('webhook_id', $webhookId)->lockForUpdate()->exists()) {
                    return 'duplicate';
                }

                $byOrder = $merchantOrderId !== ''
                    ? AlliancePePayout::query()->where('merchant_order_id', $merchantOrderId)->lockForUpdate()->first()
                    : null;
                $payout = $byOrder;
                if (! $payout && $providerId !== '') {
                    $payout = AlliancePePayout::query()->where('provider_payout_id', $providerId)->lockForUpdate()->first();
                }

                if (! $payout || ! $payout->vendor()->exists()) {
                    return 'unknown';
                }

                if ($merchantOrderId !== '' && $payout->merchant_order_id !== $merchantOrderId) {
                    return 'mismatch';
                }
                if ($providerId !== '' && $payout->provider_payout_id && $payout->provider_payout_id !== $providerId) {
                    return 'mismatch';
                }
                if (array_key_exists('amount', $data) && abs((float) $payout->amount - (float) $data['amount']) > 0.001) {
                    return 'amount';
                }

                $payouts->applyProviderStatus($payout, $data, null, 'webhook');

                AlliancePeWebhookEvent::query()->create([
                    'webhook_id' => $webhookId,
                    'event' => $event,
                    'provider_payout_id' => $providerId !== '' ? $providerId : $payout->provider_payout_id,
                    'merchant_order_id' => $merchantOrderId !== '' ? $merchantOrderId : $payout->merchant_order_id,
                ]);

                return 'applied';
            });
        } catch (QueryException $e) {
            $state = $e->errorInfo[0] ?? '';
            if (in_array($state, ['23000', '23505'], true)) {
                return response()->json(['success' => true]);
            }
            throw $e;
        }

        return match ($result) {
            'duplicate', 'applied' => response()->json(['success' => true]),
            'unknown' => response()->json(['success' => false, 'message' => 'Unknown payout reference.'], 404),
            'amount' => response()->json(['success' => false, 'message' => 'Amount mismatch.'], 422),
            default => response()->json(['success' => false, 'message' => 'Payout reference mismatch.'], 422),
        };
    }
}
