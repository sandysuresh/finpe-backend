<?php

namespace Tests\Feature;

use App\Models\AlliancePePayout;
use App\Models\AlliancePePayoutEvent;
use App\Models\AlliancePeWebhookEvent;
use App\Models\Vendor;
use App\Models\Wallet;
use App\Models\WalletLedger;
use App\Support\AlliancePeCallback;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use PDOException;
use Tests\TestCase;

class AlliancePeWebhookTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'alliancepe.environment' => 'uat',
            'alliancepe.callback_url' => 'https://finpe.test/api/webhooks/alliancepe/payouts',
            'alliancepe.uat.webhook_secret' => 'uat-webhook-secret',
            'alliancepe.live.webhook_secret' => 'live-webhook-secret',
        ]);
    }

    public function test_callback_url_uses_the_configured_https_route(): void
    {
        $this->assertSame('https://finpe.test/api/webhooks/alliancepe/payouts', AlliancePeCallback::url());
        $this->assertSame('/api/webhooks/alliancepe/payouts', route('alliancepe.webhook', [], false));

        config(['alliancepe.callback_url' => 'http://finpe.test/api/webhooks/alliancepe/payouts']);
        $this->assertNull(AlliancePeCallback::url());
    }

    public function test_callbacks_update_status_and_refund_the_original_debit_once(): void
    {
        $vendor = $this->vendor(500);
        $completed = $this->payout($vendor, 'ORDER-OK', 100, 3);
        $this->postWebhook('payout.completed', 'wh-ok', $completed, 'completed', [
            'utrNumber' => 'UTR-OK',
            'providerPayoutId' => 'PROV-OK',
        ])->assertOk();
        $completed->refresh();
        $this->assertSame('completed', $completed->status);
        $this->assertSame('UTR-OK', $completed->utr_number);
        $this->assertSame('debited', $completed->wallet_state);
        $this->assertSame('100.00', $completed->amount);
        $this->assertSame('3.00', $completed->commission_amount);
        $this->assertSame('103.00', $completed->total_debited);
        $this->assertTrue(AlliancePePayoutEvent::query()->where('alliancepe_payout_id', $completed->id)->where('source', 'webhook')->exists());

        $failed = $this->payout($vendor, 'ORDER-FAIL', 100, 3);
        $this->postWebhook('payout.failed', 'wh-fail', $failed, 'failed', ['failureReason' => 'reversed'])->assertOk();
        $this->postWebhook('payout.failed', 'wh-fail', $failed, 'failed', ['failureReason' => 'reversed'])->assertOk();
        $failed->refresh();
        $this->assertSame('failed', $failed->status);
        $this->assertSame('refunded', $failed->wallet_state);
        $this->assertSame(1, WalletLedger::query()->where('reference', 'ORDER-FAIL')->where('type', 'credit')->count());
        $this->assertSame('103.00', (string) WalletLedger::query()->where('reference', 'ORDER-FAIL')->where('type', 'credit')->value('amount'));

        $cancelled = $this->payout($vendor, 'ORDER-CANCEL', 100, 3);
        $this->postWebhook('payout.cancelled', 'wh-cancel', $cancelled, 'cancelled')->assertOk();
        $this->postWebhook('payout.cancelled', 'wh-cancel-2', $cancelled, 'cancelled')->assertOk();
        $cancelled->refresh();
        $this->assertSame('cancelled', $cancelled->status);
        $this->assertSame('refunded', $cancelled->wallet_state);
        $this->assertSame(1, WalletLedger::query()->where('reference', 'ORDER-CANCEL')->where('type', 'credit')->count());

        $rejected = $this->payout($vendor, 'ORDER-REJECT', 100, 3);
        $this->postWebhook('payout.rejected', 'wh-reject', $rejected, 'rejected', ['failureReason' => 'Rejected by admin'])->assertOk();
        $rejected->refresh();
        $this->assertSame('rejected', $rejected->status);
        $this->assertSame('debited', $rejected->wallet_state);
        $this->assertSame(0, WalletLedger::query()->where('reference', 'ORDER-REJECT')->where('type', 'credit')->count());

        $this->assertSame(294.0, (float) $vendor->wallet()->first()->balance);
    }

    public function test_invalid_unknown_and_mismatched_callbacks_do_not_change_the_wallet(): void
    {
        $vendor = $this->vendor(500);
        $payout = $this->payout($vendor, 'ORDER-SAFE', 100, 3);

        $this->postWebhook('payout.failed', 'wh-bad-sign', $payout, 'failed', [], 'wrong-secret')->assertUnauthorized();
        $missing = json_encode([
            'event' => 'payout.failed',
            'webhook_id' => 'wh-missing',
            'data' => ['merchantOrderId' => 'DOES-NOT-EXIST', 'status' => 'failed', 'amount' => 100],
        ]);
        $this->call('POST', '/api/webhooks/alliancepe/payouts', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_WEBHOOK_SIGNATURE' => hash_hmac('sha256', $missing, 'uat-webhook-secret'),
        ], $missing)->assertNotFound();

        $this->postWebhook('payout.failed', 'wh-amount', $payout, 'failed', ['amount' => 103])->assertStatus(422);

        $payout->refresh();
        $this->assertSame('pending', $payout->status);
        $this->assertSame('debited', $payout->wallet_state);
        $this->assertSame('100.00', $payout->amount);
        $this->assertSame('103.00', $payout->total_debited);
        $this->assertSame(397.0, (float) $vendor->wallet()->first()->balance);
        $this->assertSame(0, AlliancePeWebhookEvent::query()->whereIn('webhook_id', ['wh-bad-sign', 'wh-missing', 'wh-amount'])->count());
    }

    public function test_lifecycle_callbacks_advance_status_without_repeating_or_regressing(): void
    {
        $vendor = $this->vendor(500);
        $payout = $this->payout($vendor, 'ORDER-LIFE', 200, 0);
        $payout->update(['provider_payout_id' => null]);

        $this->postWebhook('payout.pending', 'wh-life-pending', $payout, 'pending')->assertOk();
        $this->postWebhook('payout.processing', 'wh-life-processing', $payout, 'processing')->assertOk();
        $this->postWebhook('payout.completed', 'wh-life-completed', $payout, 'completed', [
            'payoutId' => 'PAYOUT-LIFE',
            'utrNumber' => 'UTR-LIFE',
        ])->assertOk();
        $payout->refresh();
        $this->assertSame('completed', $payout->status);
        $this->assertSame('PAYOUT-LIFE', $payout->provider_payout_id);
        $this->assertSame('UTR-LIFE', $payout->utr_number);
        $this->assertSame('debited', $payout->wallet_state);
        $this->assertSame(300.0, (float) $vendor->wallet()->first()->balance);

        $this->postWebhook('payout.completed', 'wh-life-completed', $payout, 'completed', [
            'payoutId' => 'PAYOUT-LIFE',
            'utrNumber' => 'UTR-LIFE',
        ])->assertOk();
        $this->assertSame(1, AlliancePeWebhookEvent::query()->where('webhook_id', 'wh-life-completed')->count());
        $this->assertSame(1, AlliancePePayoutEvent::query()->where('alliancepe_payout_id', $payout->id)->where('status', 'completed')->count());

        $this->postWebhook('payout.processing', 'wh-life-completed', $payout, 'processing')->assertStatus(409);
        $payout->refresh();
        $this->assertSame('completed', $payout->status);
        $this->assertSame('UTR-LIFE', $payout->utr_number);
        $this->assertSame(300.0, (float) $vendor->wallet()->first()->balance);

        $this->postWebhook('payout.processing', 'wh-life-stale', $payout, 'processing')->assertOk();
        $payout->refresh();
        $this->assertSame('completed', $payout->status);
        $this->assertSame(0, AlliancePeWebhookEvent::query()->where('webhook_id', 'wh-life-stale')->count());

        $failed = $this->payout($vendor, 'ORDER-LIFE-FAIL', 100, 0);
        $this->postWebhook('payout.failed', 'wh-life-fail', $failed, 'failed')->assertOk();
        $this->postWebhook('payout.processing', 'wh-life-fail-old', $failed, 'processing')->assertOk();
        $failed->refresh();
        $this->assertSame('failed', $failed->status);
        $this->assertSame(0, AlliancePeWebhookEvent::query()->where('webhook_id', 'wh-life-fail-old')->count());
        $this->assertSame('refunded', $failed->wallet_state);
        $this->assertSame(1, WalletLedger::query()->where('reference', 'ORDER-LIFE-FAIL')->where('type', 'credit')->count());

        $cancelled = $this->payout($vendor, 'ORDER-LIFE-CANCEL', 50, 0);
        $this->postWebhook('payout.cancelled', 'wh-life-cancel', $cancelled, 'cancelled')->assertOk();
        $this->postWebhook('payout.pending', 'wh-life-cancel-old', $cancelled, 'pending')->assertOk();
        $cancelled->refresh();
        $this->assertSame('cancelled', $cancelled->status);
        $this->assertSame(0, AlliancePeWebhookEvent::query()->where('webhook_id', 'wh-life-cancel-old')->count());
        $this->assertSame(1, WalletLedger::query()->where('reference', 'ORDER-LIFE-CANCEL')->where('type', 'credit')->count());
    }

    public function test_rolled_back_webhook_transaction_is_not_acknowledged(): void
    {
        $vendor = $this->vendor(200);
        $payout = $this->payout($vendor, 'ORDER-ROLLBACK', 100, 0);
        $pdo = new PDOException('duplicate');
        $pdo->errorInfo = ['23000', 1062, 'Duplicate entry'];
        AlliancePeWebhookEvent::creating(function (AlliancePeWebhookEvent $event) use ($pdo) {
            if ($event->webhook_id === 'wh-rollback') {
                throw new QueryException('mysql', 'insert into alliancepe_webhook_events', [], $pdo);
            }
        });

        $this->postWebhook('payout.completed', 'wh-rollback', $payout, 'completed', [
            'utrNumber' => 'UTR-ROLLBACK',
        ])->assertStatus(500);

        $payout->refresh();
        $this->assertSame('pending', $payout->status);
        $this->assertNull($payout->utr_number);
        $this->assertSame('debited', $payout->wallet_state);
        $this->assertSame(100.0, (float) $vendor->wallet()->first()->balance);
        $this->assertSame(0, AlliancePeWebhookEvent::query()->where('webhook_id', 'wh-rollback')->count());
    }

    public function test_completed_webhook_is_idempotent_for_duplicates_prior_completion_and_conflicts(): void
    {
        $vendor = $this->vendor(1000);
        $logs = $this->captureDiagLogs();

        $first = $this->payout($vendor, 'ORDER-FIRST', 102, 0);
        $this->postWebhook('payout.completed', 'wh-first', $first, 'completed')->assertOk();
        $this->postWebhook('payout.completed', 'wh-first', $first, 'completed')->assertOk();
        $first->refresh();
        $this->assertSame('completed', $first->status);
        $this->assertSame(1, WalletLedger::query()->where('reference', 'ORDER-FIRST')->count());
        $this->assertSame(1, AlliancePeWebhookEvent::query()->where('webhook_id', 'wh-first')->count());

        $polled = $this->payout($vendor, 'ORDER-POLLED', 102, 0);
        $polled->update(['status' => 'completed']);
        $this->postWebhook('payout.completed', 'wh-polled', $polled, 'completed', ['utrNumber' => 'UTR-POLLED'])->assertOk();
        $polled->refresh();
        $this->assertSame('completed', $polled->status);
        $this->assertSame('UTR-POLLED', $polled->utr_number);
        $this->assertSame(1, AlliancePeWebhookEvent::query()->where('webhook_id', 'wh-polled')->count());
        $this->assertSame(1, WalletLedger::query()->where('reference', 'ORDER-POLLED')->count());
        $this->assertSame(0, WalletLedger::query()->where('reference', 'ORDER-POLLED')->where('type', 'credit')->count());

        $progress = $this->payout($vendor, 'ORDER-PROGRESS', 102, 0);
        $this->postWebhook('payout.pending', 'wh-progress-pending', $progress, 'pending')->assertOk();
        $this->postWebhook('payout.processing', 'wh-progress-processing', $progress, 'processing')->assertOk();
        $this->postWebhook('payout.completed', 'wh-progress-completed', $progress, 'completed')->assertOk();
        $progress->refresh();
        $this->assertSame('completed', $progress->status);

        $reused = $this->payout($vendor, 'ORDER-REUSED', 102, 0);
        $this->postWebhook('payout.processing', 'wh-reused', $reused, 'processing')->assertOk();
        $this->postWebhook('payout.processing', 'wh-reused', $reused, 'processing')->assertOk();
        $this->postWebhook('payout.completed', 'wh-reused', $reused, 'completed')->assertStatus(409);
        $reused->refresh();
        $this->assertSame('processing', $reused->status);
        $this->assertSame(1, AlliancePeWebhookEvent::query()->where('webhook_id', 'wh-reused')->count());
        $this->assertSame('payout.processing', AlliancePeWebhookEvent::query()->where('webhook_id', 'wh-reused')->value('event'));
        $this->assertSame(1, WalletLedger::query()->where('reference', 'ORDER-REUSED')->count());

        $other = $this->payout($vendor, 'ORDER-OTHER', 102, 0);
        $this->postWebhook('payout.completed', 'wh-reused', $other, 'completed')->assertStatus(422);
        $other->refresh();
        $this->assertSame('pending', $other->status);
        $this->assertSame('processing', $reused->fresh()->status);
        $this->assertSame('payout.processing', AlliancePeWebhookEvent::query()->where('webhook_id', 'wh-reused')->value('event'));

        $this->postWebhook('payout.completed', 'wh-first', $first, 'completed', ['amount' => 1])->assertStatus(422);
        $this->postWebhook('payout.completed', 'wh-first', $first, 'completed', [
            'merchantOrderId' => 'ORDER-OTHER',
        ])->assertStatus(422);
        $this->postWebhook('payout.completed', 'wh-first', $first, 'completed', [
            'payoutId' => 'PAY-SOMEONE-ELSE',
        ])->assertStatus(422);
        $first->refresh();
        $this->assertSame('completed', $first->status);
        $this->assertSame('102.00', $first->amount);

        $this->postWebhook('payout.completed', 'wh-bad-hmac', $first, 'completed', [], 'wrong-secret')->assertUnauthorized();

        $failed = $this->payout($vendor, 'ORDER-FAIL-ONCE', 102, 0);
        $this->postWebhook('payout.failed', 'wh-fail-once', $failed, 'failed')->assertOk();
        $this->postWebhook('payout.failed', 'wh-fail-once', $failed, 'failed')->assertOk();
        $this->postWebhook('payout.completed', 'wh-fail-once', $failed, 'completed')->assertStatus(409);
        $failed->refresh();
        $this->assertSame('failed', $failed->status);
        $this->assertSame('refunded', $failed->wallet_state);
        $this->assertSame(1, WalletLedger::query()->where('reference', 'ORDER-FAIL-ONCE')->where('type', 'credit')->count());

        $rejected = $this->payout($vendor, 'ORDER-REJECT-ONCE', 102, 0);
        $this->postWebhook('payout.rejected', 'wh-reject-once', $rejected, 'rejected')->assertOk();
        $this->postWebhook('payout.processing', 'wh-reject-stale', $rejected, 'processing')->assertOk();
        $rejected->refresh();
        $this->assertSame('rejected', $rejected->status);
        $this->assertSame(0, AlliancePeWebhookEvent::query()->where('webhook_id', 'wh-reject-stale')->count());
        $this->assertSame('debited', $rejected->wallet_state);
        $this->assertSame(0, WalletLedger::query()->where('reference', 'ORDER-REJECT-ONCE')->where('type', 'credit')->count());

        $race = $this->payout($vendor, 'ORDER-RACE', 102, 0);
        $race->update(['status' => 'completed']);
        AlliancePeWebhookEvent::query()->create([
            'webhook_id' => 'wh-race',
            'event' => 'payout.completed',
            'provider_payout_id' => $race->provider_payout_id,
            'merchant_order_id' => $race->merchant_order_id,
        ]);
        $hide = true;
        AlliancePeWebhookEvent::addGlobalScope('hide-race', function ($query) use (&$hide) {
            if ($hide) {
                $query->where('webhook_id', '!=', 'wh-race');
            }
        });
        $pdo = new PDOException('duplicate');
        $pdo->errorInfo = ['23000', 1062, 'Duplicate entry'];
        AlliancePeWebhookEvent::creating(function (AlliancePeWebhookEvent $event) use (&$hide, $pdo) {
            if ($event->webhook_id === 'wh-race') {
                $hide = false;
                throw new QueryException('mysql', 'insert into alliancepe_webhook_events', [], $pdo);
            }
        });
        $this->postWebhook('payout.completed', 'wh-race', $race, 'completed')->assertOk();
        $race->refresh();
        $this->assertSame('completed', $race->status);
        $this->assertSame(1, WalletLedger::query()->where('reference', 'ORDER-RACE')->count());
        $hide = false;

        $results = array_map(fn (array $entry) => $entry['context']['result'] ?? null, array_filter(
            $logs->getArrayCopy(),
            fn (array $entry) => ($entry['context']['stage'] ?? null) === 'response',
        ));
        $this->assertContains('applied', $results);
        $this->assertContains('duplicate', $results);
        $this->assertContains('already_completed', $results);
        $this->assertContains('invalid_transition', $results);
        $this->assertContains('reference_mismatch', $results);
        $this->assertContains('amount_mismatch', $results);
        $this->assertContains('signature_failed', $results);
    }

    public function test_stale_new_webhook_ids_are_acknowledged_without_an_event_or_financial_change(): void
    {
        $vendor = $this->vendor(2000);
        $logs = $this->captureDiagLogs();
        $cases = [
            ['completed', 'processing', 'ORDER-STALE-COMPLETED'],
            ['failed', 'pending', 'ORDER-STALE-FAILED'],
            ['cancelled', 'on_hold', 'ORDER-STALE-CANCELLED'],
            ['rejected', 'processing', 'ORDER-STALE-REJECTED'],
        ];

        foreach ($cases as [$terminal, $incoming, $order]) {
            $payout = $this->payout($vendor, $order, 102, 0);
            $payout->update(['status' => $terminal]);
            $ledger = WalletLedger::query()->where('reference', $order)->count();
            $balance = (float) $vendor->wallet()->first()->balance;
            $webhookId = 'wh-stale-'.$terminal;

            $this->postWebhook('payout.'.$incoming, $webhookId, $payout, $incoming)->assertOk();

            $payout->refresh();
            $this->assertSame($terminal, $payout->status);
            $this->assertSame('debited', $payout->wallet_state);
            $this->assertSame(0, AlliancePeWebhookEvent::query()->where('webhook_id', $webhookId)->count());
            $this->assertSame($ledger, WalletLedger::query()->where('reference', $order)->count());
            $this->assertSame(0, WalletLedger::query()->where('reference', $order)->where('type', 'credit')->count());
            $this->assertSame($balance, (float) $vendor->wallet()->first()->balance);
        }

        $results = array_map(fn (array $entry) => $entry['context']['result'] ?? null, array_filter(
            $logs->getArrayCopy(),
            fn (array $entry) => ($entry['context']['stage'] ?? null) === 'response',
        ));
        $this->assertContains('invalid_transition', $results);
        $this->assertTrue(collect($logs->getArrayCopy())->contains(fn (array $entry) => ($entry['context']['stage'] ?? null) === 'status'
            && ($entry['context']['apply_called'] ?? null) === false
            && ($entry['context']['result'] ?? null) === 'invalid_transition'));
    }

    public function test_diagnostic_logs_are_sanitized_for_applied_duplicate_rejected_and_rollback(): void
    {
        $vendor = $this->vendor(400);
        $payout = $this->payout($vendor, 'ORDER-DIAG', 100, 0);
        $secret = 'uat-webhook-secret';
        $account = '998877665544';
        $email = 'leak-check@example.com';
        $logs = $this->captureDiagLogs();

        $this->postWebhook('payout.completed', 'wh-diag', $payout, 'completed', [
            'utrNumber' => 'UTR-DIAG',
            'accountNumber' => $account,
            'beneficiaryEmail' => $email,
            'ifsc' => 'HDFC0001307',
            'phone' => '9998887776',
        ])->assertOk();
        $this->postWebhook('payout.completed', 'wh-diag', $payout, 'completed', [
            'utrNumber' => 'UTR-DIAG',
        ])->assertOk();

        $badBody = json_encode([
            'event' => 'payout.completed',
            'webhook_id' => 'wh-diag-bad',
            'data' => [
                'merchantOrderId' => 'ORDER-DIAG',
                'status' => 'completed',
                'amount' => 100,
                'accountNumber' => $account,
            ],
        ]);
        $badSignature = hash_hmac('sha256', $badBody, 'wrong-secret');
        $this->call('POST', '/api/webhooks/alliancepe/payouts', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_WEBHOOK_SIGNATURE' => $badSignature,
        ], $badBody)->assertUnauthorized();

        $pdo = new PDOException('duplicate');
        $pdo->errorInfo = ['23000', 1062, 'Duplicate entry'];
        AlliancePeWebhookEvent::creating(function (AlliancePeWebhookEvent $event) use ($pdo) {
            if ($event->webhook_id === 'wh-diag-rollback') {
                throw new QueryException('mysql', 'insert into alliancepe_webhook_events', [], $pdo);
            }
        });
        $this->postWebhook('payout.completed', 'wh-diag-rollback', $payout, 'completed')->assertStatus(500);

        $encoded = json_encode($logs->getArrayCopy());
        $this->assertIsString($encoded);
        $this->assertStringNotContainsString($secret, $encoded);
        $this->assertStringNotContainsString($badSignature, $encoded);
        $this->assertStringNotContainsString($account, $encoded);
        $this->assertStringNotContainsString($email, $encoded);
        $this->assertStringNotContainsString('HDFC0001307', $encoded);
        $this->assertStringNotContainsString('9998887776', $encoded);
        $this->assertStringNotContainsString('UTR-DIAG', $encoded);

        $entries = $logs->getArrayCopy();
        $results = array_map(fn (array $entry) => $entry['context']['result'] ?? null, array_filter(
            $entries,
            fn (array $entry) => ($entry['context']['stage'] ?? null) === 'response',
        ));
        $this->assertContains('applied', $results);
        $this->assertContains('duplicate', $results);
        $this->assertContains('signature_failed', $results);
        $this->assertContains('database_error', $results);
        $this->assertTrue(collect($entries)->contains(fn (array $entry) => ($entry['context']['transaction'] ?? null) === 'rolled_back'
            && ($entry['context']['exception'] ?? null) === QueryException::class
            && ($entry['context']['result'] ?? null) === 'database_error'));
        $this->assertTrue(collect($entries)->contains(fn (array $entry) => ($entry['context']['stage'] ?? null) === 'signature'
            && ($entry['context']['signature_result'] ?? null) === 'FAIL'
            && ($entry['context']['secret'] ?? null) === 'SET'));
        $this->assertTrue(collect($entries)->contains(fn (array $entry) => ($entry['context']['stage'] ?? null) === 'status'
            && ($entry['context']['apply_called'] ?? null) === true
            && ($entry['context']['after_status'] ?? null) === 'completed'));
    }

    private function captureDiagLogs(): \ArrayObject
    {
        $logs = new \ArrayObject();
        Log::listen(function ($event) use ($logs) {
            if ($event->message !== 'ALLIANCEPE_WEBHOOK_DIAG') {
                return;
            }
            $logs->append([
                'level' => $event->level,
                'message' => $event->message,
                'context' => $event->context,
            ]);
        });

        return $logs;
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function postWebhook(string $event, string $id, AlliancePePayout $payout, string $status, array $extra = [], string $secret = 'uat-webhook-secret')
    {
        $payload = [
            'event' => $event,
            'webhook_id' => $id,
            'data' => array_merge([
                'payoutId' => $payout->provider_payout_id,
                'merchantOrderId' => $payout->merchant_order_id,
                'status' => $status,
                'amount' => (float) $payout->amount,
            ], $extra),
        ];
        $body = json_encode($payload);

        return $this->call('POST', '/api/webhooks/alliancepe/payouts', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_WEBHOOK_SIGNATURE' => hash_hmac('sha256', $body, $secret),
        ], $body);
    }

    private function payout(Vendor $vendor, string $order, float $amount, float $commission): AlliancePePayout
    {
        $total = number_format($amount + $commission, 2, '.', '');
        $wallet = $vendor->wallet()->first();
        $before = (float) $wallet->balance;
        $after = $before - ($amount + $commission);
        $wallet->update(['balance' => $after]);
        WalletLedger::query()->create([
            'vendor_id' => $vendor->id,
            'wallet_id' => $wallet->id,
            'type' => 'debit',
            'amount' => $total,
            'balance_before' => $before,
            'balance_after' => $after,
            'reference' => $order,
            'description' => 'AlliancePe payout',
            'source' => 'alliancepe',
        ]);

        return AlliancePePayout::query()->create([
            'vendor_id' => $vendor->id,
            'environment' => 'uat',
            'merchant_order_id' => $order,
            'provider_payout_id' => 'PAY-'.$order,
            'amount' => number_format($amount, 2, '.', ''),
            'commission_amount' => number_format($commission, 2, '.', ''),
            'total_debited' => $total,
            'status' => 'pending',
            'wallet_state' => 'debited',
            'beneficiary_name' => 'Webhook Beneficiary',
            'bank_name' => 'Example Bank',
            'account_number' => '123456789012',
            'ifsc_code' => 'HDFC0001307',
            'phone' => '9876543210',
            'email' => 'beneficiary@example.com',
            'transfer_mode' => 'imps',
        ]);
    }

    private function vendor(float $balance): Vendor
    {
        $code = 'WH-'.uniqid();
        $vendor = Vendor::query()->create([
            'vendor_code' => $code,
            'business_name' => 'Webhook '.$code,
            'contact_name' => 'Owner',
            'email' => strtolower($code).'@finpe.test',
            'password' => 'password',
            'phone' => '9800000000',
            'kyc_status' => 'verified',
            'status' => 'active',
        ]);
        Wallet::query()->create(['vendor_id' => $vendor->id, 'balance' => $balance]);

        return $vendor;
    }
}
