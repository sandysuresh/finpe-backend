<?php

namespace App\Livewire\Admin;

use App\Exceptions\PayoutException;
use App\Http\Controllers\Api\V1\PayoutCallbackController;
use App\Http\Controllers\Api\V1\PayoutController;
use App\Http\Controllers\Api\V1\PayoutMasterController;
use App\Livewire\Admin\PayoutTransactions;
use App\Models\ApiCredential;
use App\Models\CommissionEntry;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\VendorApiAccess;
use App\Models\WalletLedger;
use App\Services\Aeps\VimoPayClient;
use App\Services\CommissionService;
use App\Services\Payout\PayoutMasterProvider;
use App\Services\Payout\PayoutProviderRegistry;
use App\Services\PayoutService;
use App\Support\CommissionProviders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

class PayoutApiTest extends Component
{
    private const ACCOUNT_MIN_LENGTH = 9;

    private const ACCOUNT_MAX_LENGTH = 18;
    public int $step = 1;

    public ?int $vendorId = null;

    public string $vendorSearch = '';

    /** @var array<int, string> */
    public array $stepResults = [];

    /** @var array{http_status: int|null, success: bool, message: string, token_received: bool, response_ms: int|null}|null */
    public ?array $authResult = null;

    /** @var array{success: bool, http_status: int|null, message: string, total: int, banks: list<array{code: string, description: string}>, response_ms: int|null}|null */
    public ?array $bankResult = null;

    /** @var array{success: bool, http_status: int|null, message: string, total: int, purposes: list<array{code: string, description: string}>, response_ms: int|null}|null */
    public ?array $purposeResult = null;

    /** @var array{success: bool, http_status: int|null, message: string, total: int, states: list<array{code: string, description: string}>, response_ms: int|null}|null */
    public ?array $stateResult = null;

    /** @var array{outcome: string, http_status: int|null, message: string, reference: ?string, merchant_ref: ?string, provider_reference: ?string, provider_status: ?string, amount: float|null, charge: float|null, total_debited: float|null, wallet_before: float|null, wallet_after: float|null, account_mask: string, mobile_mask: string, response_ms: int|null}|null */
    public ?array $payoutResult = null;

    /** @var array{success: bool, http_status: int|null, message: string, mapped_status: ?string, response_ms: int|null}|null */
    public ?array $statusCheckResult = null;

    /** @var array{success: bool, http_status: int|null, acknowledgement: string, status: ?string, wallet_effect: string, commission_effect: string, idempotency: string}|null */
    public ?array $callbackCheckResult = null;

    public bool $statusCheckPassed = false;

    public bool $callbackCheckPassed = false;

    /** @var list<array{code: string, description: string}> */
    public array $payoutBanks = [];

    /** @var list<array{code: string, description: string}> */
    public array $payoutPurposes = [];

    /** @var list<array{code: string, description: string}> */
    public array $payoutStates = [];

    public bool $payoutCatalogsLoaded = false;

    public bool $payoutConfirming = false;

    public string $beneficiaryName = '';

    public string $beneficiaryAccount = '';

    public string $beneficiaryIfsc = '';

    public string $beneficiaryBank = '';

    public string $beneficiaryMobile = '';

    public string $beneficiaryState = '';

    public string $paymentPurpose = '';

    public string $paymentMode = 'IMPS';

    public string $amount = '';

    public string $merchantRefId = '';

    public bool $merchantRefSubmitted = false;

    public string $payoutLat = '';

    public string $payoutLong = '';

    private ?Vendor $resolvedVendor = null;

    private bool $vendorResolved = false;

    public function mount(): void
    {
        $this->authorizeModule();
    }

    public function updatedVendorId(mixed $value): void
    {
        $this->vendorId = is_numeric($value) && (int) $value > 0 ? (int) $value : null;
        $this->resolvedVendor = null;
        $this->vendorResolved = false;
        $this->step = 1;
        $this->authResult = null;
        $this->bankResult = null;
        $this->purposeResult = null;
        $this->stateResult = null;
        $this->payoutResult = null;
        $this->statusCheckResult = null;
        $this->callbackCheckResult = null;
        $this->statusCheckPassed = false;
        $this->callbackCheckPassed = false;
        $this->payoutBanks = [];
        $this->payoutPurposes = [];
        $this->payoutStates = [];
        $this->payoutCatalogsLoaded = false;
        $this->payoutConfirming = false;
        $this->beneficiaryName = '';
        $this->beneficiaryAccount = '';
        $this->beneficiaryIfsc = '';
        $this->beneficiaryMobile = '';
        $this->beneficiaryBank = '';
        $this->beneficiaryState = '';
        $this->paymentPurpose = '';
        $this->paymentMode = 'IMPS';
        $this->amount = '';
        $this->merchantRefId = '';
        $this->merchantRefSubmitted = false;
        $this->payoutLat = '';
        $this->payoutLong = '';
        $this->stepResults = [];
    }

    public function updatedVendorSearch(): void
    {
        $this->vendorSearch = mb_substr(trim($this->vendorSearch), 0, 80);
    }

    public function go(int $step): void
    {
        if ($step < 1 || $step > count($this->steps()) || ! $this->stepUnlocked($step)) {
            return;
        }

        $this->step = $step;
    }

    public function next(): void
    {
        $this->go($this->step + 1);
    }

    public function previous(): void
    {
        $this->go($this->step - 1);
    }

    public function testAuthorization(VimoPayClient $client): void
    {
        $this->authorizeModule();

        $error = $this->blockReason();
        if ($error !== null) {
            $this->authResult = [
                'http_status' => null,
                'success' => false,
                'message' => $error,
                'token_received' => false,
                'response_ms' => null,
            ];
            $this->stepResults[1] = 'failed';

            return;
        }

        $result = $client->testAuthorization();
        $this->authResult = [
            'http_status' => $result['http_status'] ?? null,
            'success' => ($result['success'] ?? false) === true,
            'message' => $this->safeResultMessage($result['message'] ?? null, ($result['success'] ?? false) === true),
            'token_received' => ($result['token_received'] ?? false) === true,
            'response_ms' => isset($result['response_ms']) ? (int) $result['response_ms'] : null,
        ];
        $this->stepResults[1] = $this->authResult['success'] ? 'pass' : 'failed';
    }

    public function testBankList(PayoutMasterProvider $master): void
    {
        $this->authorizeModule();

        if (($this->stepResults[1] ?? null) !== 'pass') {
            $this->recordBankResult(false, null, 'Step 1 Authorization must pass before the bank list can be tested.', 0, [], null);

            return;
        }

        $error = $this->blockReason();
        if ($error !== null) {
            $this->recordBankResult(false, null, $error, 0, [], null);

            return;
        }

        $started = hrtime(true);
        $response = app(PayoutMasterController::class)->banks($master);
        $elapsed = (int) round((hrtime(true) - $started) / 1_000_000);
        $payload = $response->getData(true);
        $payload = is_array($payload) ? $payload : [];
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $banks = [];
        foreach (array_slice($data, 0, 5) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $banks[] = [
                'code' => $this->plainBankField($row['code'] ?? null),
                'description' => $this->plainBankField($row['description'] ?? null),
            ];
        }

        $success = $response->getStatusCode() < 400 && ($payload['success'] ?? false) === true;
        $this->recordBankResult(
            $success,
            $response->getStatusCode(),
            $this->safeResultMessage($payload['message'] ?? null, $success),
            count($data),
            $banks,
            $elapsed,
        );
        $this->payoutBanks = $success ? $this->catalogRows(['data' => $data], null) : [];
    }

    public function testPurposeList(PayoutMasterProvider $master): void
    {
        $this->authorizeModule();

        if (($this->stepResults[2] ?? null) !== 'pass') {
            $this->recordPurposeResult(false, null, 'Step 2 Bank List must pass before the purpose list can be tested.', 0, [], null);

            return;
        }

        $error = $this->blockReason();
        if ($error !== null) {
            $this->recordPurposeResult(false, null, $error, 0, [], null);

            return;
        }

        $started = hrtime(true);
        $response = app(PayoutMasterController::class)->purposes($master);
        $elapsed = (int) round((hrtime(true) - $started) / 1_000_000);
        $payload = $response->getData(true);
        $payload = is_array($payload) ? $payload : [];
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $purposes = [];
        foreach (array_slice($data, 0, 5) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $purposes[] = [
                'code' => $this->plainBankField($row['code'] ?? null),
                'description' => $this->plainBankField($row['description'] ?? null),
            ];
        }

        $success = $response->getStatusCode() < 400 && ($payload['success'] ?? false) === true;
        $this->recordPurposeResult(
            $success,
            $response->getStatusCode(),
            $this->safeResultMessage($payload['message'] ?? null, $success, 'Purpose list loaded.', 'Payout provider request failed.'),
            count($data),
            $purposes,
            $elapsed,
        );
    }

    public function testStateList(PayoutMasterProvider $master): void
    {
        $this->authorizeModule();

        if (($this->stepResults[3] ?? null) !== 'pass') {
            $this->recordStateResult(false, null, 'Step 3 Purpose of Payout must pass before the state list can be tested.', 0, [], null);

            return;
        }

        $error = $this->blockReason();
        if ($error !== null) {
            $this->recordStateResult(false, null, $error, 0, [], null);

            return;
        }

        $started = hrtime(true);
        $response = app(PayoutMasterController::class)->states($master);
        $elapsed = (int) round((hrtime(true) - $started) / 1_000_000);
        $payload = $response->getData(true);
        $payload = is_array($payload) ? $payload : [];
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $states = [];
        foreach (array_slice($data, 0, 5) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $states[] = [
                'code' => $this->plainBankField($row['code'] ?? null),
                'description' => $this->plainBankField($row['description'] ?? null),
            ];
        }

        $success = $response->getStatusCode() < 400 && ($payload['success'] ?? false) === true;
        $this->recordStateResult(
            $success,
            $response->getStatusCode(),
            $this->safeResultMessage($payload['message'] ?? null, $success, 'State list loaded.', 'Payout provider request failed.'),
            count($data),
            $states,
            $elapsed,
        );
    }

    public function reviewPayout(): void
    {
        $this->authorizeModule();
        $this->ensurePayoutCatalogs();

        $error = $this->payoutGate() ?? $this->validatePayoutForm();
        if ($error !== null) {
            $this->recordPayoutFailure($error, null);

            return;
        }

        if ($this->merchantRefSubmitted || trim($this->merchantRefId) === '') {
            $this->merchantRefId = $this->freshMerchantRef();
        }

        $this->merchantRefSubmitted = false;
        $this->payoutConfirming = true;
    }

    public function cancelPayoutReview(): void
    {
        $this->payoutConfirming = false;
    }

    public function confirmPayout(PayoutService $payouts): void
    {
        $this->authorizeModule();

        if (! $this->payoutConfirming) {
            return;
        }

        $this->payoutConfirming = false;
        $this->ensurePayoutCatalogs();

        $error = $this->payoutGate() ?? $this->validatePayoutForm();
        if ($error !== null) {
            $this->recordPayoutFailure($error, null);

            return;
        }

        $vendor = $this->activeVendor();
        if (! $vendor) {
            $this->recordPayoutFailure('The selected vendor is not active.', null);

            return;
        }

        $vendor->loadMissing('wallet');
        $before = $vendor->wallet ? round((float) $vendor->wallet->balance, 2) : null;
        $payload = [
            'merchantRefId' => trim($this->merchantRefId),
            'amount' => round((float) $this->amount, 2),
            'beneficiaryBank' => trim($this->beneficiaryBank),
            'paymentPurpose' => $this->paymentPurpose,
            'paymentMode' => strtoupper($this->paymentMode),
            'beneficiaryAccountNumber' => trim($this->beneficiaryAccount),
            'beneficiaryIFSC' => strtoupper(trim($this->beneficiaryIfsc)),
            'beneficiaryMobileNumber' => trim($this->beneficiaryMobile),
            'beneficiaryName' => trim($this->beneficiaryName),
            'beneficiaryLocation' => $this->beneficiaryState,
            'lat' => trim($this->payoutLat),
            'long' => trim($this->payoutLong),
            'udf1' => '',
            'udf2' => '',
            'udf3' => '',
        ];

        $started = hrtime(true);
        $this->merchantRefSubmitted = true;
        try {
            $txn = $payouts->transfer($vendor, $payload);
        } catch (PayoutException $e) {
            $elapsed = (int) round((hrtime(true) - $started) / 1_000_000);
            $vendor->unsetRelation('wallet');
            $vendor->load('wallet');
            $after = $vendor->wallet ? round((float) $vendor->wallet->balance, 2) : $before;
            $this->recordPayoutFailure(
                $this->safeResultMessage($e->getMessage(), false, 'Payout successful.', 'Payout request failed.'),
                $e->statusCode,
                $before,
                $after,
                $elapsed,
            );
            $this->merchantRefId = '';

            return;
        } catch (Throwable) {
            $elapsed = (int) round((hrtime(true) - $started) / 1_000_000);
            $this->recordPayoutFailure('Payout request failed.', null, $before, $before, $elapsed);
            $this->merchantRefId = '';

            return;
        }

        $elapsed = (int) round((hrtime(true) - $started) / 1_000_000);
        $vendor->unsetRelation('wallet');
        $vendor->load('wallet');
        $after = $vendor->wallet ? round((float) $vendor->wallet->balance, 2) : null;
        $status = (string) $txn->status;
        $outcome = match ($status) {
            'success' => 'pass',
            'pending' => 'pending',
            default => 'failed',
        };
        $message = match ($status) {
            'success' => 'Payout successful.',
            'pending' => 'Payout initiated.',
            default => $this->safeResultMessage($txn->failure_reason, false, 'Payout successful.', 'Payout failed.'),
        };

        $this->payoutResult = [
            'outcome' => $outcome,
            'http_status' => 201,
            'message' => $message,
            'reference' => $txn->reference,
            'merchant_ref' => $txn->merchant_ref,
            'provider_reference' => $txn->bank_reference,
            'provider_status' => $status,
            'amount' => (float) $txn->amount,
            'charge' => (float) $txn->payout_charge,
            'total_debited' => round((float) $txn->amount + (float) $txn->payout_charge, 2),
            'wallet_before' => $before,
            'wallet_after' => $after,
            'account_mask' => PayoutTransactions::maskAccountStatic($txn->account_number),
            'mobile_mask' => PayoutTransactions::maskMobileStatic($txn->beneficiary_mobile),
            'response_ms' => $elapsed,
        ];
        $this->stepResults[5] = $outcome;
        if ($outcome === 'failed') {
            $this->merchantRefId = '';
        }
    }

    public function checkTransactionStatus(): void
    {
        $this->authorizeModule();
        $txn = $this->stepFiveTransaction();
        if (! $txn) {
            $this->statusCheckResult = [
                'success' => false,
                'http_status' => null,
                'message' => 'Step 5 must pass before the transaction status can be checked.',
                'mapped_status' => null,
                'response_ms' => null,
            ];
            $this->statusCheckPassed = false;
            $this->refreshStepSix();

            return;
        }

        $started = hrtime(true);
        $request = Request::create('/api/v1/payouts/'.$txn->reference, 'GET');
        $request->attributes->set('apiVendor', $txn->vendor);
        $response = app(PayoutController::class)->show($request, $txn->reference);
        $elapsed = (int) round((hrtime(true) - $started) / 1_000_000);
        $payload = $response->getData(true);
        $payload = is_array($payload) ? $payload : [];
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $mapped = is_string($data['status'] ?? null) ? $data['status'] : null;
        $providerMessage = $data['failure_reason'] ?? null;
        if (! is_string($providerMessage) || trim($providerMessage) === '') {
            $providerMessage = $this->payoutResult['message'] ?? ($payload['message'] ?? null);
        }
        $message = $this->safeResultMessage($providerMessage, $response->isSuccessful(), 'Status loaded.', 'Transaction status could not be loaded.');
        $success = $response->isSuccessful() && ($payload['success'] ?? false) === true && ($data['reference'] ?? null) === $txn->reference;

        $this->statusCheckResult = [
            'success' => $success,
            'http_status' => $response->getStatusCode(),
            'message' => $message,
            'mapped_status' => $mapped,
            'response_ms' => $elapsed,
        ];
        $this->statusCheckPassed = $success;
        $this->refreshStepSix();
    }

    public function testCallback(): void
    {
        $this->authorizeModule();
        $txn = $this->stepFiveTransaction();
        if (! $txn) {
            $this->recordCallbackFailure(null, 'Step 5 must pass before the callback can be tested.');

            return;
        }

        $built = $this->callbackPayload($txn);
        if (is_string($built)) {
            $this->recordCallbackFailure(null, $built);

            return;
        }

        $vendor = $txn->vendor()->with('wallet')->first();
        $walletBefore = $vendor?->wallet ? round((float) $vendor->wallet->balance, 2) : null;
        $commissionBefore = $this->commissionCount($txn);
        $transactionCount = Transaction::query()
            ->where('vendor_id', $txn->vendor_id)
            ->where('merchant_ref', $txn->merchant_ref)
            ->count();

        $first = $this->sendCallback($built);
        if (! $first['ok']) {
            $this->recordCallbackFailure($first['http_status'], $first['message'], $txn->fresh()->status);

            return;
        }

        $second = $this->sendCallback($built);
        $txn->refresh();
        $vendor?->wallet?->refresh();
        $walletAfter = $vendor?->wallet ? round((float) $vendor->wallet->balance, 2) : null;
        $commissionAfter = $this->commissionCount($txn);
        $transactionAfter = Transaction::query()
            ->where('vendor_id', $txn->vendor_id)
            ->where('merchant_ref', $txn->merchant_ref)
            ->count();

        $walletSame = $walletBefore === $walletAfter;
        $commissionAdded = $commissionAfter - $commissionBefore;
        $noDuplicateTxn = $transactionAfter === $transactionCount;
        $idempotent = $second['ok'] && $walletSame && $commissionAdded <= 1 && $noDuplicateTxn && $commissionAfter <= $commissionBefore + 1;

        $this->callbackCheckResult = [
            'success' => $first['ok'] && $idempotent,
            'http_status' => $first['http_status'],
            'acknowledgement' => $first['message'],
            'status' => $txn->status,
            'wallet_effect' => $walletSame
                ? 'Wallet balance unchanged'.($walletAfter !== null ? ' at ₹'.number_format($walletAfter, 2) : '')
                : 'Wallet balance changed from ₹'.number_format((float) $walletBefore, 2).' to ₹'.number_format((float) $walletAfter, 2),
            'commission_effect' => $commissionAdded === 0
                ? 'No commission entry was added. '.$commissionAfter.' recorded for this payout.'
                : ($commissionAdded === 1
                    ? 'One commission entry was recorded by the existing callback. The repeat did not add another.'
                    : 'Commission entries changed by '.$commissionAdded.'.'),
            'idempotency' => $idempotent
                ? 'The repeat callback was accepted and did not create a second transaction, a duplicate commission entry, or another wallet movement.'
                : 'The repeat callback did not stay idempotent.',
        ];
        $this->callbackCheckPassed = $first['ok'] && $idempotent;
        $this->refreshStepSix();
    }

    public function stepStatus(int $step): string
    {
        return match ($this->stepResults[$step] ?? null) {
            'pass' => 'PASS',
            'failed' => 'FAILED',
            'pending' => 'PENDING',
            default => 'NOT TESTED',
        };
    }

    public function stepUnlocked(int $step): bool
    {
        if ($step <= 1) {
            return true;
        }

        if ($step > count($this->steps()) || $this->blockReason() !== null) {
            return false;
        }

        for ($previous = 1; $previous < $step; $previous++) {
            if (($this->stepResults[$previous] ?? null) !== 'pass') {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<array{title: string, purpose: string}>
     */
    public function steps(): array
    {
        return [
            ['title' => 'Authorization', 'purpose' => 'Confirm authorization for the selected vendor before any later payout API call.'],
            ['title' => 'Bank List', 'purpose' => 'Load the payout bank list used to choose a beneficiary bank code.'],
            ['title' => 'Purpose of Payout', 'purpose' => 'Load the allowed payout purposes for the payment purpose code.'],
            ['title' => 'State List', 'purpose' => 'Load the state list used for the beneficiary location.'],
            ['title' => 'Payout Transaction', 'purpose' => 'Submit one payout transfer and review the FinPe reference, charge, and provider status.'],
            ['title' => 'Callback / Transaction Status', 'purpose' => 'Check the payout status after the provider callback or a status lookup.'],
            ['title' => 'Test Summary', 'purpose' => 'Review which steps were tested and the result of this payout API run.'],
        ];
    }

    public function render(PayoutProviderRegistry $providers)
    {
        $steps = $this->steps();
        $current = $steps[$this->step - 1];
        if ($this->step === 5) {
            $this->ensurePayoutCatalogs();
        }

        $vendor = $this->activeVendor();
        $environment = strtoupper(trim((string) config('services.vimopay.environment')));

        try {
            $providerCode = strtolower($providers->current()->code());
        } catch (Throwable) {
            $providerCode = strtolower((string) config('payout.provider'));
        }
        $providerName = CommissionProviders::name($providerCode) ?? ($providerCode !== '' ? $providerCode : 'VimoPay');

        return view('livewire.admin.payout-api-test', [
            'steps' => $steps,
            'current' => $current,
            'total' => count($steps),
            'vendors' => $this->vendorOptions(),
            'selectedBank' => $this->selectedBank(),
            'selected' => $vendor ? [
                'name' => $vendor->business_name,
                'code' => $vendor->vendor_code,
                'payout_access' => $vendor->hasEnabledApi(VendorApiAccess::PAYOUT) ? 'Enabled' : 'Disabled',
                'credential_status' => $this->credentialStatus($vendor->apiCredential),
            ] : null,
            'blockReason' => $this->blockReason($vendor),
            'providerName' => $providerName,
            'environment' => $environment !== '' ? $environment : 'UAT',
            'payoutEstimate' => $this->payoutEstimate($vendor, $providerCode),
            'payoutSnapshot' => $this->step === 6 ? $this->payoutSnapshot() : null,
            'testReport' => $this->step === 7 ? $this->testReport() : null,
        ])->layout('layouts.admin', ['title' => 'Payout API']);
    }

    private function authorizeModule(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('payout-api')) {
            abort(403);
        }
    }

    private function activeVendor(): ?Vendor
    {
        if ($this->vendorResolved) {
            return $this->resolvedVendor;
        }

        $this->vendorResolved = true;
        $this->resolvedVendor = $this->vendorId
            ? Vendor::query()->with('apiCredential')->where('status', 'active')->find($this->vendorId)
            : null;

        return $this->resolvedVendor;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Vendor>
     */
    private function vendorOptions()
    {
        $search = str_replace(['%', '_'], ['\\%', '\\_'], trim($this->vendorSearch));
        $vendors = Vendor::query()
            ->where('status', 'active')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('business_name', 'like', '%'.$search.'%')
                        ->orWhere('vendor_code', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('business_name')
            ->limit(100)
            ->get(['id', 'business_name', 'vendor_code']);

        if ($this->vendorId && ! $vendors->contains('id', $this->vendorId)) {
            $selected = Vendor::query()
                ->where('status', 'active')
                ->find($this->vendorId, ['id', 'business_name', 'vendor_code']);
            if ($selected) {
                $vendors->prepend($selected);
            }
        }

        return $vendors;
    }

    private function credentialStatus(?ApiCredential $credential): string
    {
        if (! $credential) {
            return 'Not issued';
        }

        return $credential->is_active && $credential->hmacSecret() !== '' ? 'Active' : 'Inactive';
    }

    private function blockReason(?Vendor $vendor = null): ?string
    {
        if (! $this->vendorId) {
            return 'Select an active vendor before testing.';
        }

        $vendor ??= Vendor::query()->with('apiCredential')->find($this->vendorId);
        if (! $vendor || $vendor->status !== 'active') {
            return 'The selected vendor is not active.';
        }

        if (! $vendor->hasEnabledApi(VendorApiAccess::PAYOUT)) {
            return 'Payout API access is disabled for this vendor.';
        }

        $credential = $vendor->apiCredential;
        if (! $credential || ! $credential->is_active || $credential->hmacSecret() === '') {
            return 'The API credential is inactive.';
        }

        return null;
    }

    /**
     * @param  list<array{code: string, description: string}>  $banks
     */
    private function recordBankResult(bool $success, ?int $httpStatus, string $message, int $total, array $banks, ?int $responseMs): void
    {
        $this->bankResult = [
            'success' => $success,
            'http_status' => $httpStatus,
            'message' => $message,
            'total' => $total,
            'banks' => $banks,
            'response_ms' => $responseMs,
        ];
        $this->stepResults[2] = $success ? 'pass' : 'failed';
        if (! $success) {
            $this->payoutBanks = [];
        }
    }

    /**
     * @param  list<array{code: string, description: string}>  $purposes
     */
    private function recordPurposeResult(bool $success, ?int $httpStatus, string $message, int $total, array $purposes, ?int $responseMs): void
    {
        $this->purposeResult = [
            'success' => $success,
            'http_status' => $httpStatus,
            'message' => $message,
            'total' => $total,
            'purposes' => $purposes,
            'response_ms' => $responseMs,
        ];
        $this->stepResults[3] = $success ? 'pass' : 'failed';
    }

    /**
     * @param  list<array{code: string, description: string}>  $states
     */
    private function recordStateResult(bool $success, ?int $httpStatus, string $message, int $total, array $states, ?int $responseMs): void
    {
        $this->stateResult = [
            'success' => $success,
            'http_status' => $httpStatus,
            'message' => $message,
            'total' => $total,
            'states' => $states,
            'response_ms' => $responseMs,
        ];
        $this->stepResults[4] = $success ? 'pass' : 'failed';
    }

    private function plainBankField(mixed $value): string
    {
        $value = is_scalar($value) ? trim((string) $value) : '';
        if ($value === '' || preg_match('/bearer\s+[A-Za-z0-9._\-]+|sk_|pk_|secret|salt|encrypt/i', $value) === 1) {
            return '';
        }

        return mb_substr($value, 0, 120);
    }

    private function safeResultMessage(mixed $message, bool $success, string $successFallback = 'Authorization succeeded.', string $failureFallback = 'Authorization failed.'): string
    {
        $message = is_string($message) ? trim($message) : '';
        if ($message === '' || preg_match('/bearer\s+[A-Za-z0-9._\-]+|sk_|pk_|secret_key|salt_key|encryptdecrypt/i', $message) === 1) {
            return $success ? $successFallback : $failureFallback;
        }

        return $message;
    }

    private function ensurePayoutCatalogs(): void
    {
        if ($this->payoutCatalogsLoaded || ($this->stepResults[4] ?? null) !== 'pass') {
            return;
        }

        $this->payoutCatalogsLoaded = true;
        try {
            $master = app(PayoutMasterProvider::class);
            $this->payoutPurposes = $this->catalogRows($master->purposes());
            $this->payoutStates = $this->catalogRows($master->states());
        } catch (Throwable) {
            $this->payoutPurposes = [];
            $this->payoutStates = [];
        }

        if ($this->payoutBanks === [] && ($this->stepResults[2] ?? null) === 'pass') {
            $this->payoutBanks = $this->previewRows($this->bankResult['banks'] ?? []);
        }
        if ($this->payoutPurposes === []) {
            $this->payoutPurposes = $this->previewRows($this->purposeResult['purposes'] ?? []);
        }
        if ($this->payoutStates === []) {
            $this->payoutStates = $this->previewRows($this->stateResult['states'] ?? []);
        }
    }

    /**
     * @param  array{data?: mixed}  $result
     * @return list<array{code: string, description: string}>
     */
    private function catalogRows(array $result, ?int $limit = 500): array
    {
        $rows = [];
        $data = is_array($result['data'] ?? null) ? $result['data'] : [];
        foreach ($data as $row) {
            if (! is_array($row)) {
                continue;
            }
            $code = $this->plainBankField($row['code'] ?? null);
            if ($code === '') {
                continue;
            }
            $rows[] = [
                'code' => $code,
                'description' => $this->plainBankField($row['description'] ?? null),
            ];
            if ($limit !== null && count($rows) >= $limit) {
                break;
            }
        }

        return $rows;
    }

    /**
     * @param  mixed  $rows
     * @return list<array{code: string, description: string}>
     */
    private function previewRows(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $clean = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $code = $this->plainBankField($row['code'] ?? null);
            if ($code === '') {
                continue;
            }
            $clean[] = [
                'code' => $code,
                'description' => $this->plainBankField($row['description'] ?? null),
            ];
        }

        return $clean;
    }

    private function payoutGate(): ?string
    {
        if (($this->stepResults[4] ?? null) !== 'pass') {
            return 'Step 4 State List must pass before a payout can be tested.';
        }

        return $this->blockReason();
    }

    private function validatePayoutForm(): ?string
    {
        if (trim($this->beneficiaryName) === '' || mb_strlen(trim($this->beneficiaryName)) > 120) {
            return 'Beneficiary name is required.';
        }
        $account = trim($this->beneficiaryAccount);
        if ($account === '') {
            return 'Beneficiary account number is required.';
        }
        if (preg_match('/^\d{'.self::ACCOUNT_MIN_LENGTH.','.self::ACCOUNT_MAX_LENGTH.'}$/', $account) !== 1) {
            return 'Beneficiary account number must be '.self::ACCOUNT_MIN_LENGTH.' to '.self::ACCOUNT_MAX_LENGTH.' digits.';
        }
        $ifsc = strtoupper(trim($this->beneficiaryIfsc));
        if ($ifsc === '') {
            return 'Beneficiary IFSC is required.';
        }
        if (preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc) !== 1) {
            return 'Beneficiary IFSC must be 11 characters: 4 letters, 0, then 6 letters or digits.';
        }
        $bankCode = trim($this->beneficiaryBank);
        if ($bankCode === '' || ! $this->catalogHas($this->payoutBanks, $bankCode)) {
            return $bankCode === ''
                ? 'Select a bank code from the Step 2 bank list.'
                : 'Bank code '.mb_substr($bankCode, 0, 20).' is not in the Step 2 bank list.';
        }
        $mobile = trim($this->beneficiaryMobile);
        if ($mobile === '') {
            return 'Beneficiary mobile is required.';
        }
        if (preg_match('/^\d{10}$/', $mobile) !== 1) {
            return 'Beneficiary mobile must be a 10-digit number.';
        }
        if (! $this->catalogHas($this->payoutStates, $this->beneficiaryState)) {
            return 'Choose a state from the tested state list.';
        }
        if (! $this->catalogHas($this->payoutPurposes, $this->paymentPurpose)) {
            return 'Choose a purpose from the tested purpose list.';
        }
        if (! in_array(strtoupper($this->paymentMode), ['IMPS', 'NEFT'], true)) {
            return 'Payment mode must be IMPS or NEFT.';
        }
        if (! is_numeric($this->amount)) {
            return 'Amount is required.';
        }
        $amount = round((float) $this->amount, 2);
        if ($amount < 100 || $amount >= 100000) {
            return 'Amount must be at least 100 and below 100000.';
        }
        if (trim($this->merchantRefId) !== '' && strlen(trim($this->merchantRefId)) > 40) {
            return 'Merchant reference must be 40 characters or less.';
        }
        $latitude = $this->coordinateError($this->payoutLat, 'Latitude', -90, 90);
        if ($latitude !== null) {
            return $latitude;
        }
        $longitude = $this->coordinateError($this->payoutLong, 'Longitude', -180, 180);
        if ($longitude !== null) {
            return $longitude;
        }

        return null;
    }

    private function coordinateError(string $value, string $label, float $min, float $max): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return $label.' is required.';
        }
        if (preg_match('/^-?\d+(?:\.\d+)?$/', $value) !== 1) {
            return $label.' must be a number from '.$min.' to '.$max.'.';
        }
        $number = (float) $value;
        if ($number < $min || $number > $max) {
            return $label.' must be a number from '.$min.' to '.$max.'.';
        }

        return null;
    }

    /**
     * @param  list<array{code: string, description: string}>  $rows
     */
    /**
     * @return array{code: string, description: string}|null
     */
    private function selectedBank(): ?array
    {
        $code = trim($this->beneficiaryBank);
        foreach ($this->payoutBanks as $bank) {
            if (($bank['code'] ?? '') === $code) {
                return $bank;
            }
        }

        return null;
    }

    private function catalogHas(array $rows, string $code): bool
    {
        if ($code === '') {
            return false;
        }

        foreach ($rows as $row) {
            if (($row['code'] ?? '') === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{passed: bool, steps: list<array{title: string, status: string, summary: string}>, payout: array<string, string>|null}
     */
    private function testReport(): array
    {
        $passed = true;
        for ($number = 1; $number <= 6; $number++) {
            if (($this->stepResults[$number] ?? null) !== 'pass') {
                $passed = false;
                break;
            }
        }

        $txn = $passed ? $this->stepFiveTransaction() : null;
        $entry = $txn?->commissionEntry;
        $debit = $txn
            ? WalletLedger::query()->where('vendor_id', $txn->vendor_id)->where('reference', $txn->reference)->where('type', 'debit')->orderBy('id')->first()
            : null;

        return [
            'passed' => $passed && $txn !== null,
            'steps' => [
                [
                    'title' => 'Authorization',
                    'status' => $this->stepStatus(1),
                    'summary' => $this->authResult
                        ? 'HTTP '.($this->authResult['http_status'] ?? '—').' · '.($this->authResult['message'] ?? '').' · Token '.(($this->authResult['token_received'] ?? false) ? 'Yes' : 'No')
                        : 'Not tested in this session.',
                ],
                [
                    'title' => 'Bank List',
                    'status' => $this->stepStatus(2),
                    'summary' => $this->bankResult
                        ? 'HTTP '.($this->bankResult['http_status'] ?? '—').' · '.($this->bankResult['total'] ?? 0).' banks · '.($this->bankResult['message'] ?? '')
                        : 'Not tested in this session.',
                ],
                [
                    'title' => 'Purpose of Payout',
                    'status' => $this->stepStatus(3),
                    'summary' => $this->purposeResult
                        ? 'HTTP '.($this->purposeResult['http_status'] ?? '—').' · '.($this->purposeResult['total'] ?? 0).' purposes · '.($this->purposeResult['message'] ?? '')
                        : 'Not tested in this session.',
                ],
                [
                    'title' => 'State List',
                    'status' => $this->stepStatus(4),
                    'summary' => $this->stateResult
                        ? 'HTTP '.($this->stateResult['http_status'] ?? '—').' · '.($this->stateResult['total'] ?? 0).' states · '.($this->stateResult['message'] ?? '')
                        : 'Not tested in this session.',
                ],
                [
                    'title' => 'Payout Transaction',
                    'status' => $this->stepStatus(5),
                    'summary' => $this->payoutResult
                        ? ($this->payoutResult['reference'] ?: '—').' · '.ucfirst((string) ($this->payoutResult['provider_status'] ?? 'unknown')).' · ₹'.number_format((float) ($this->payoutResult['amount'] ?? 0), 2)
                        : 'Not tested in this session.',
                ],
                [
                    'title' => 'Transaction Status',
                    'status' => $this->statusCheckPassed ? 'PASS' : ($this->statusCheckResult ? 'FAILED' : 'NOT TESTED'),
                    'summary' => $this->statusCheckResult
                        ? 'HTTP '.($this->statusCheckResult['http_status'] ?? '—').' · '.ucfirst((string) ($this->statusCheckResult['mapped_status'] ?? 'unknown')).' · '.($this->statusCheckResult['message'] ?? '')
                        : 'Not tested in this session.',
                ],
                [
                    'title' => 'Callback / Idempotency',
                    'status' => $this->callbackCheckPassed ? 'PASS' : ($this->callbackCheckResult ? 'FAILED' : 'NOT TESTED'),
                    'summary' => $this->callbackCheckResult
                        ? 'HTTP '.($this->callbackCheckResult['http_status'] ?? '—').' · '.($this->callbackCheckResult['acknowledgement'] ?? '').' · '.($this->callbackCheckResult['idempotency'] ?? '')
                        : 'Not tested in this session.',
                ],
            ],
            'payout' => $txn ? [
                'reference' => (string) $txn->reference,
                'merchant_ref' => (string) ($txn->merchant_ref ?: '—'),
                'provider_reference' => (string) ($txn->bank_reference ?: '—'),
                'rrn' => (string) ($txn->rrn ?: '—'),
                'amount' => '₹'.number_format((float) $txn->amount, 2),
                'charge' => '₹'.number_format((float) $txn->payout_charge, 2),
                'total' => '₹'.number_format((float) $txn->amount + (float) $txn->payout_charge, 2),
                'status' => ucfirst((string) $txn->status),
                'wallet_debit' => $debit ? '₹'.number_format((float) $debit->amount, 2) : '—',
                'commission' => $entry
                    ? 'Entry '.$entry->id.' · ₹'.number_format((float) $entry->commission_amount, 2).' · '.ucfirst((string) $entry->status)
                    : '—',
            ] : null,
        ];
    }

    private function stepFiveTransaction(): ?Transaction
    {
        $reference = $this->payoutResult['reference'] ?? null;
        if (! is_string($reference) || $reference === '' || ($this->stepResults[5] ?? null) !== 'pass') {
            return null;
        }

        $vendor = $this->activeVendor();
        if (! $vendor) {
            return null;
        }

        return Transaction::query()
            ->where('vendor_id', $vendor->id)
            ->where('type', 'payout')
            ->where('reference', $reference)
            ->first();
    }

    /**
     * @return array{reference: ?string, merchant_ref: ?string, provider_reference: ?string, rrn: ?string, status: ?string, message: ?string, amount: float|null, charge: float|null, total: float|null, provider: string}|null
     */
    private function payoutSnapshot(): ?array
    {
        $txn = $this->stepFiveTransaction();
        if (! $txn) {
            return null;
        }

        return [
            'reference' => $txn->reference,
            'merchant_ref' => $txn->merchant_ref,
            'provider_reference' => $txn->bank_reference,
            'rrn' => $txn->rrn,
            'status' => $txn->status,
            'message' => $txn->failure_reason ?: ($this->payoutResult['message'] ?? null),
            'amount' => (float) $txn->amount,
            'charge' => (float) $txn->payout_charge,
            'total' => round((float) $txn->amount + (float) $txn->payout_charge, 2),
            'provider' => CommissionProviders::name($txn->payout_provider) ?? (string) $txn->payout_provider,
        ];
    }

    /**
     * @return array<string, mixed>|string
     */
    private function callbackPayload(Transaction $txn)
    {
        $providerReference = trim((string) $txn->bank_reference);
        if ($providerReference === '') {
            return 'The provider reference was not stored, so the callback was not sent.';
        }

        [$code, $reported] = match ($txn->status) {
            'success' => ['000', 'Success'],
            default => [null, null],
        };
        if ($code === null || $reported === null) {
            return 'The recorded status does not map to one provider status code, so the callback was not sent.';
        }

        $rrn = trim((string) $txn->rrn);
        if ($rrn === '') {
            return 'The provider RRN was not stored with this payout. The real callback requires a later provider callback containing the RRN.';
        }

        foreach (['beneficiary_mobile', 'beneficiary_bank_code', 'account_number', 'ifsc_code', 'beneficiary_location', 'beneficiary_name', 'merchant_ref', 'payment_purpose', 'service'] as $field) {
            if (trim((string) $txn->{$field}) === '') {
                return 'The payout is missing stored details, so the callback was not sent.';
            }
        }

        if (trim($this->payoutLat) === '' || trim($this->payoutLong) === '') {
            return 'Latitude and longitude from the payout test are no longer available, so the callback was not sent.';
        }

        $message = trim((string) ($txn->failure_reason ?: ($this->payoutResult['message'] ?? '')));
        if ($message === '') {
            return 'The provider message was not stored, so the callback was not sent.';
        }

        return [
            'beneficiaryMobileNumber' => (string) $txn->beneficiary_mobile,
            'beneficiaryBank' => (string) $txn->beneficiary_bank_code,
            'beneficiaryAccountNumber' => (string) $txn->account_number,
            'beneficiaryIFSC' => (string) $txn->ifsc_code,
            'beneficiaryLocation' => (string) $txn->beneficiary_location,
            'beneficiaryName' => (string) $txn->beneficiary_name,
            'merchantRefId' => (string) $txn->merchant_ref,
            'amount' => (float) $txn->amount,
            'charges' => (float) $txn->payout_charge,
            'paymentMode' => strtoupper((string) $txn->service),
            'paymentPurpose' => (string) $txn->payment_purpose,
            'lat' => trim($this->payoutLat),
            'long' => trim($this->payoutLong),
            'udf1' => '',
            'udf2' => '',
            'udf3' => '',
            'txnStatus' => $reported,
            'txnStatusCode' => $code,
            'txnId' => $providerReference,
            'rrn' => $rrn,
            'responseMessage' => mb_substr($message, 0, 255),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, http_status: int|null, message: string}
     */
    private function sendCallback(array $payload): array
    {
        $request = Request::create('/api/v1/payout/callback', 'POST', $payload);
        try {
            $response = app(PayoutCallbackController::class)($request, app(PayoutService::class));
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();

            return [
                'ok' => false,
                'http_status' => 422,
                'message' => $this->safeResultMessage(is_string($message) ? $message : null, false, 'Callback accepted.', 'Callback payload was rejected.'),
            ];
        }

        $body = $response->getData(true);
        $body = is_array($body) ? $body : [];
        $ok = $response->isSuccessful() && ($body['successStatus'] ?? false) === true;

        return [
            'ok' => $ok,
            'http_status' => $response->getStatusCode(),
            'message' => $this->safeResultMessage($body['message'] ?? null, $ok, 'Success', 'Invalid callback.'),
        ];
    }

    private function commissionCount(Transaction $txn): int
    {
        return CommissionEntry::query()
            ->where('source_type', CommissionService::SOURCE_TRANSACTIONS)
            ->where('source_id', $txn->id)
            ->count();
    }

    private function recordCallbackFailure(?int $httpStatus, string $message, ?string $status = null): void
    {
        $this->callbackCheckResult = [
            'success' => false,
            'http_status' => $httpStatus,
            'acknowledgement' => $message,
            'status' => $status,
            'wallet_effect' => 'Wallet was not changed by this test.',
            'commission_effect' => 'No commission entry was added by this test.',
            'idempotency' => 'The callback was not repeated.',
        ];
        $this->callbackCheckPassed = false;
        $this->refreshStepSix();
    }

    private function refreshStepSix(): void
    {
        if ($this->statusCheckPassed && $this->callbackCheckPassed) {
            $this->stepResults[6] = 'pass';

            return;
        }

        if ($this->statusCheckResult !== null && ! $this->statusCheckPassed) {
            $this->stepResults[6] = 'failed';

            return;
        }

        if ($this->callbackCheckResult !== null && ! $this->callbackCheckPassed) {
            $this->stepResults[6] = 'failed';
        }
    }

    private function freshMerchantRef(): string
    {
        return 'UAT-'.now()->format('YmdHis').'-'.Str::lower(Str::random(6));
    }

    private function recordPayoutFailure(string $message, ?int $httpStatus, ?float $before = null, ?float $after = null, ?int $responseMs = null): void
    {
        $this->payoutResult = [
            'outcome' => 'failed',
            'http_status' => $httpStatus,
            'message' => $message,
            'reference' => null,
            'merchant_ref' => trim($this->merchantRefId) !== '' ? trim($this->merchantRefId) : null,
            'provider_reference' => null,
            'provider_status' => null,
            'amount' => is_numeric($this->amount) ? round((float) $this->amount, 2) : null,
            'charge' => null,
            'total_debited' => null,
            'wallet_before' => $before,
            'wallet_after' => $after,
            'account_mask' => PayoutTransactions::maskAccountStatic($this->beneficiaryAccount),
            'mobile_mask' => PayoutTransactions::maskMobileStatic($this->beneficiaryMobile),
            'response_ms' => $responseMs,
        ];
        $this->stepResults[5] = 'failed';
        $this->payoutConfirming = false;
    }

    /**
     * @return array{rule: string, charge: float|null, total: float|null, wallet: float|null, wallet_after: float|null}|null
     */
    private function payoutEstimate(?Vendor $vendor, string $providerCode): ?array
    {
        if (! $vendor || $providerCode === '') {
            return null;
        }

        $vendor->loadMissing('wallet');
        $wallet = $vendor->wallet ? round((float) $vendor->wallet->balance, 2) : null;
        $amount = is_numeric($this->amount) ? round((float) $this->amount, 2) : null;
        $commission = app(CommissionService::class);
        $rule = $commission->select($commission->rulesForVendor((int) $vendor->id), [
            'vendor_id' => (int) $vendor->id,
            'merchant_id' => null,
            'provider' => $providerCode,
            'type' => 'payout',
            'at' => now(),
        ]);

        $ruleLabel = 'No payout commission rule. Charge ₹0.00';
        $charge = $amount !== null ? 0.0 : null;
        if ($rule) {
            $ruleLabel = $rule->calc_type === 'percentage'
                ? rtrim(rtrim(number_format((float) $rule->value, 2, '.', ''), '0'), '.').'%'
                : 'Fixed ₹'.number_format((float) $rule->value, 2);
            if ($amount !== null) {
                $charge = (float) $commission->calculate(
                    (string) $rule->calc_type,
                    $commission->money($amount),
                    $commission->money($rule->value),
                );
            } elseif ($rule->calc_type === 'fixed') {
                $charge = (float) $rule->value;
            }
        }

        $total = $amount !== null && $charge !== null ? round($amount + $charge, 2) : null;

        return [
            'rule' => $ruleLabel,
            'charge' => $charge,
            'total' => $total,
            'wallet' => $wallet,
            'wallet_after' => $wallet !== null && $total !== null ? round($wallet - $total, 2) : null,
        ];
    }
}
