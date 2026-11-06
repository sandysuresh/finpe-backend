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
use Illuminate\Support\Facades\Log;
use Throwable;

class AlliancePeWebhookController extends Controller
{
    /** @var list<string> */
    private const STATUSES = ['pending', 'processing', 'completed', 'failed', 'cancelled', 'rejected', 'on_hold'];

    private const DIAG = 'ALLIANCEPE_WEBHOOK_DIAG';

    public function __invoke(Request $request, AlliancePeClient $client, AlliancePePayoutService $payouts): JsonResponse
    {
        $raw = $request->getContent();
        $signatureHeader = trim((string) $request->header('X-Webhook-Signature', ''));
        $signature = strtolower($signatureHeader);
        $signature = str_starts_with($signature, 'sha256=') ? substr($signature, 7) : $signature;
        $fields = $this->correlation($raw, $request);

        try {
            $environment = $client->environment();
        } catch (Throwable $e) {
            $this->diag('error', $fields + [
                'stage' => 'signature',
                'environment' => null,
                'result' => 'error',
                'exception' => $e::class,
            ]);

            throw $e;
        }

        $secret = config('alliancepe.'.$environment.'.webhook_secret');
        $secretSet = is_string($secret) && $secret !== '';
        $this->diag('info', $fields + [
            'stage' => 'received',
            'content_length' => strlen($raw),
            'body_sha256' => hash('sha256', $raw),
        ]);

        if (! $secretSet || $signature === '') {
            $this->diag('warning', $fields + [
                'stage' => 'signature',
                'environment' => $environment,
                'secret' => $secretSet ? 'SET' : 'NOT SET',
                'signature_header' => $signatureHeader !== '' ? 'present' : 'absent',
                'signature_result' => 'FAIL',
            ]);

            return $this->finish($fields, 'signature_failed', response()->json(['success' => false, 'message' => 'Invalid webhook signature.'], 401));
        }

        $expected = hash_hmac('sha256', $raw, $secret);
        if (! hash_equals($expected, $signature)) {
            $this->diag('warning', $fields + [
                'stage' => 'signature',
                'environment' => $environment,
                'secret' => 'SET',
                'signature_header' => 'present',
                'signature_result' => 'FAIL',
            ]);

            return $this->finish($fields, 'signature_failed', response()->json(['success' => false, 'message' => 'Invalid webhook signature.'], 401));
        }

        $this->diag('info', $fields + [
            'stage' => 'signature',
            'environment' => $environment,
            'secret' => 'SET',
            'signature_header' => 'present',
            'signature_result' => 'PASS',
        ]);

        $payload = json_decode($raw, true);
        if (! is_array($payload)) {
            return $this->finish($fields, 'rejected', response()->json(['success' => false, 'message' => 'Invalid webhook.'], 400));
        }

        $event = (string) ($payload['event'] ?? $request->header('X-Webhook-Event', ''));
        $webhookId = (string) ($payload['webhook_id'] ?? $request->header('X-Webhook-ID', ''));
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $fields = $this->correlation($raw, $request);
        if ($webhookId === '' || ! str_starts_with($event, 'payout.')) {
            return $this->finish($fields, 'rejected', response()->json(['success' => false, 'message' => 'Invalid webhook.'], 400));
        }

        $status = strtolower((string) ($data['status'] ?? substr($event, strlen('payout.'))));
        if (! in_array($status, self::STATUSES, true)) {
            return $this->finish($fields, 'rejected', response()->json(['success' => false, 'message' => 'Invalid webhook.'], 400));
        }
        $data['status'] = $status;
        $fields['incoming_status'] = $status;

        $merchantOrderId = is_scalar($data['merchantOrderId'] ?? null) ? trim((string) $data['merchantOrderId']) : '';
        $providerId = '';
        foreach (['payoutId', 'providerPayoutId', 'id'] as $key) {
            if (is_scalar($data[$key] ?? null) && trim((string) $data[$key]) !== '') {
                $providerId = trim((string) $data[$key]);
                break;
            }
        }
        $fields['merchant_order_id'] = $merchantOrderId !== '' ? $merchantOrderId : null;
        $fields['provider_payout_id'] = $providerId !== '' ? $providerId : null;

        if ($merchantOrderId === '' && $providerId === '') {
            return $this->finish($fields, 'error', response()->json(['success' => false, 'message' => 'Unknown payout reference.'], 404));
        }

        try {
            $result = DB::transaction(function () use ($payouts, $data, $event, $webhookId, $merchantOrderId, $providerId, $status, $fields) {
                $this->diag('info', $fields + ['stage' => 'transaction', 'transaction' => 'started']);
                $existing = AlliancePeWebhookEvent::query()->where('webhook_id', $webhookId)->lockForUpdate()->first();
                if ($existing) {
                    $decision = $this->classifyExisting($existing, $data, $event, $merchantOrderId, $providerId);
                    $stored = $this->lockedPayout(
                        (string) ($existing->merchant_order_id ?: $merchantOrderId),
                        (string) ($existing->provider_payout_id ?: $providerId),
                    );
                    $this->diag('info', $fields + [
                        'stage' => 'deduplication',
                        'webhook_id_exists' => true,
                        'existing_event' => $existing->event,
                        'stored_status' => $stored?->status,
                        'incoming_status' => $status,
                        'decision' => $decision,
                    ]);

                    return $decision;
                }

                $this->diag('info', $fields + [
                    'stage' => 'deduplication',
                    'webhook_id_exists' => false,
                    'existing_event' => null,
                    'incoming_status' => $status,
                    'decision' => 'continue',
                ]);

                $payout = $this->lockedPayout($merchantOrderId, $providerId);

                if (! $payout || ! $payout->vendor()->exists()) {
                    $this->diag('warning', $fields + [
                        'stage' => 'lookup',
                        'payout_found' => false,
                        'payout_id' => null,
                    ]);

                    return 'unknown';
                }

                $this->diag('info', $fields + [
                    'stage' => 'lookup',
                    'payout_found' => true,
                    'payout_id' => $payout->id,
                    'stored_status' => $payout->status,
                    'utr_present' => is_string($payout->utr_number) && $payout->utr_number !== '',
                ]);

                if ($merchantOrderId !== '' && $payout->merchant_order_id !== $merchantOrderId) {
                    return 'reference_mismatch';
                }
                if ($providerId !== '' && $payout->provider_payout_id && $payout->provider_payout_id !== $providerId) {
                    return 'reference_mismatch';
                }
                if (array_key_exists('amount', $data) && abs((float) $payout->amount - (float) $data['amount']) > 0.001) {
                    return 'amount_mismatch';
                }
                if ($this->isStaleNonTerminal($payout->status, $status)) {
                    $this->diag('info', $fields + [
                        'stage' => 'status',
                        'apply_called' => false,
                        'before_status' => $payout->status,
                        'incoming_status' => $status,
                        'after_status' => $payout->status,
                        'utr_updated' => false,
                        'provider_reference_updated' => false,
                        'result' => 'invalid_transition',
                    ]);

                    return 'stale';
                }

                $outcome = $this->applyAndLog($payouts, $payout, $data, $status, $fields);
                AlliancePeWebhookEvent::query()->create([
                    'webhook_id' => $webhookId,
                    'event' => $event,
                    'provider_payout_id' => $providerId !== '' ? $providerId : $payout->provider_payout_id,
                    'merchant_order_id' => $merchantOrderId !== '' ? $merchantOrderId : $payout->merchant_order_id,
                ]);

                return $outcome === 'unchanged' ? 'stale' : $outcome;
            });
            $this->diag('info', $fields + ['stage' => 'transaction', 'transaction' => 'committed']);
        } catch (QueryException $e) {
            $state = $e->errorInfo[0] ?? '';
            $this->diag('error', $fields + [
                'stage' => 'transaction',
                'transaction' => 'rolled_back',
                'exception' => $e::class,
                'sqlstate' => is_string($state) ? $state : null,
                'result' => 'database_error',
            ]);
            if (in_array($state, ['23000', '23505'], true) && $this->alreadyApplied($webhookId, $data, $event, $merchantOrderId, $providerId)) {
                return $this->finish($fields, 'duplicate', response()->json(['success' => true]));
            }
            $this->diag('error', $fields + [
                'stage' => 'response',
                'http_status' => 500,
                'result' => 'database_error',
            ]);
            throw $e;
        }

        return match ($result) {
            'duplicate' => $this->finish($fields, 'duplicate', response()->json(['success' => true])),
            'applied' => $this->finish($fields, 'applied', response()->json(['success' => true])),
            'already_completed' => $this->finish($fields, 'already_completed', response()->json(['success' => true])),
            'stale' => $this->finish($fields, 'invalid_transition', response()->json(['success' => true])),
            'invalid_transition' => $this->finish($fields, 'invalid_transition', response()->json(['success' => false, 'message' => 'Webhook status conflicts with the recorded payout.'], 409)),
            'unknown' => $this->finish($fields, 'error', response()->json(['success' => false, 'message' => 'Unknown payout reference.'], 404)),
            'amount_mismatch' => $this->finish($fields, 'amount_mismatch', response()->json(['success' => false, 'message' => 'Amount mismatch.'], 422)),
            default => $this->finish($fields, 'reference_mismatch', response()->json(['success' => false, 'message' => 'Payout reference mismatch.'], 422)),
        };
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function finish(array $fields, string $result, JsonResponse $response): JsonResponse
    {
        $status = $response->getStatusCode();
        $this->diag($status >= 500 ? 'error' : ($status >= 400 ? 'warning' : 'info'), [
            'stage' => 'response',
            'http_status' => $status,
            'result' => $result,
            'merchant_order_id' => $fields['merchant_order_id'] ?? null,
            'webhook_id' => $fields['webhook_id'] ?? null,
        ]);

        return $response;
    }

    /**
     * @return array{event: ?string, webhook_id: ?string, merchant_order_id: ?string, provider_payout_id: ?string, incoming_status: ?string}
     */
    private function correlation(string $raw, Request $request): array
    {
        $payload = json_decode($raw, true);
        $data = is_array($payload) && is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $providerId = '';
        foreach (['payoutId', 'providerPayoutId', 'id'] as $key) {
            if (is_scalar($data[$key] ?? null) && trim((string) $data[$key]) !== '') {
                $providerId = trim((string) $data[$key]);
                break;
            }
        }
        $event = is_array($payload) ? (string) ($payload['event'] ?? $request->header('X-Webhook-Event', '')) : (string) $request->header('X-Webhook-Event', '');
        $webhookId = is_array($payload) ? (string) ($payload['webhook_id'] ?? $request->header('X-Webhook-ID', '')) : (string) $request->header('X-Webhook-ID', '');
        $status = strtolower((string) ($data['status'] ?? (str_starts_with($event, 'payout.') ? substr($event, strlen('payout.')) : '')));

        return [
            'event' => $event !== '' ? $event : null,
            'webhook_id' => $webhookId !== '' ? $webhookId : null,
            'merchant_order_id' => is_scalar($data['merchantOrderId'] ?? null) && trim((string) $data['merchantOrderId']) !== '' ? trim((string) $data['merchantOrderId']) : null,
            'provider_payout_id' => $providerId !== '' ? $providerId : null,
            'incoming_status' => $status !== '' ? $status : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function diag(string $level, array $context): void
    {
        $context['at'] = now()->toIso8601String();
        match ($level) {
            'error' => Log::error(self::DIAG, $context),
            'warning' => Log::warning(self::DIAG, $context),
            default => Log::info(self::DIAG, $context),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $fields
     */
    private function applyAndLog(AlliancePePayoutService $payouts, AlliancePePayout $payout, array $data, string $status, array $fields): string
    {
        $beforeStatus = $payout->status;
        $beforeUtr = $payout->utr_number;
        $beforeProvider = $payout->provider_payout_id;
        $payouts->applyProviderStatus($payout, $data, null, 'webhook');
        $fresh = $payout->fresh();
        $this->diag('info', $fields + [
            'stage' => 'status',
            'apply_called' => true,
            'before_status' => $beforeStatus,
            'incoming_status' => $status,
            'after_status' => $fresh?->status,
            'utr_updated' => $fresh !== null && $fresh->utr_number !== $beforeUtr,
            'provider_reference_updated' => $fresh !== null && $fresh->provider_payout_id !== $beforeProvider,
        ]);
        if ($beforeStatus === $status) {
            return $status === 'completed' ? 'already_completed' : 'duplicate';
        }
        if ($fresh !== null && $fresh->status === $beforeStatus) {
            return 'unchanged';
        }

        return 'applied';
    }

    private function isStaleNonTerminal(string $current, string $incoming): bool
    {
        $terminal = ['completed', 'failed', 'cancelled', 'rejected'];

        return in_array($current, $terminal, true) && ! in_array($incoming, $terminal, true);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function classifyExisting(AlliancePeWebhookEvent $existing, array $data, string $event, string $merchantOrderId, string $providerId): string
    {
        $stored = $this->lockedPayout((string) ($existing->merchant_order_id ?: ''), (string) ($existing->provider_payout_id ?: ''));
        $incoming = $this->lockedPayout($merchantOrderId, $providerId);
        if ($stored && $incoming && $stored->id !== $incoming->id) {
            return 'reference_mismatch';
        }
        $payout = $incoming ?: $stored;
        if (! $payout || ! $payout->vendor()->exists()) {
            return 'unknown';
        }
        if ($merchantOrderId !== '' && $payout->merchant_order_id !== $merchantOrderId) {
            return 'reference_mismatch';
        }
        if ($existing->merchant_order_id && $merchantOrderId !== '' && $existing->merchant_order_id !== $merchantOrderId) {
            return 'reference_mismatch';
        }
        if ($providerId !== '' && $payout->provider_payout_id && $payout->provider_payout_id !== $providerId) {
            return 'reference_mismatch';
        }
        if ($existing->provider_payout_id && $providerId !== '' && $existing->provider_payout_id !== $providerId) {
            return 'reference_mismatch';
        }
        if (array_key_exists('amount', $data) && abs((float) $payout->amount - (float) $data['amount']) > 0.001) {
            return 'amount_mismatch';
        }
        if ($existing->event !== $event) {
            return 'invalid_transition';
        }

        return 'duplicate';
    }

    private function lockedPayout(string $merchantOrderId, string $providerId): ?AlliancePePayout
    {
        $payout = $merchantOrderId !== ''
            ? AlliancePePayout::query()->where('merchant_order_id', $merchantOrderId)->lockForUpdate()->first()
            : null;
        if (! $payout && $providerId !== '') {
            $payout = AlliancePePayout::query()->where('provider_payout_id', $providerId)->lockForUpdate()->first();
        }

        return $payout;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function alreadyApplied(string $webhookId, array $data, string $event, string $merchantOrderId, string $providerId): bool
    {
        $existing = AlliancePeWebhookEvent::query()->where('webhook_id', $webhookId)->first();
        if (! $existing) {
            return false;
        }

        return $this->classifyExisting($existing, $data, $event, $merchantOrderId, $providerId) === 'duplicate';
    }
}
