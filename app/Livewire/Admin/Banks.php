<?php

namespace App\Livewire\Admin;

use App\Models\Bank;
use App\Models\BankApiEndpoint;
use App\Models\Vendor;
use App\Services\Banking\BankGatewayManager;
use App\Support\BankApiCatalog;
use App\Support\OutboundUrl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Component;
use Livewire\WithPagination;

class Banks extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;
    public bool $showAssignModal = false;
    public bool $showApisModal = false;
    public bool $showEndpointForm = false;
    public ?int $editingId = null;
    public ?int $assignBankId = null;
    public ?int $apisBankId = null;
    public ?int $editingEndpointId = null;

    public string $name = '';
    public string $code = '';
    public string $driver = 'http';
    public string $environment = 'sandbox';
    public string $base_url = '';
    public string $username = '';
    public string $password = '';
    public string $api_key = '';
    public string $api_secret = '';
    public array $services = ['imps', 'neft', 'rtgs'];
    public bool $is_active = true;
    public bool $is_default = false;
    public string $assignSearch = '';
    public array $assignedVendorIds = [];
    public string $testMessage = '';
    public string $endpointName = '';
    public string $endpointSlug = '';
    public string $endpointMethod = 'POST';
    public string $endpointBankPath = '';
    public string $endpointDescription = '';
    public array $requestParams = [];
    public array $responseParams = [];
    public string $sampleRequestJson = '{}';
    public string $sampleResponseJson = '{}';
    public bool $endpointActive = true;

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('banks')) {
            abort(403);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $bank = Bank::findOrFail($id);
        $this->editingId = $bank->id;
        $this->name = $bank->name;
        $this->code = $bank->code;
        $this->driver = $bank->driver;
        $this->environment = $bank->environment;
        $this->base_url = $bank->base_url ?? '';
        $this->username = '';
        $this->password = '';
        $this->api_key = '';
        $this->api_secret = '';
        $this->services = $bank->services ?: ['imps', 'neft', 'rtgs'];
        $this->is_active = $bank->is_active;
        $this->is_default = $bank->is_default;
        $this->testMessage = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->code = strtoupper(trim($this->code));

        $this->validate([
            'name' => 'required|string|max:120',
            'code' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique('banks', 'code')->ignore($this->editingId),
            ],
            'driver' => 'required|in:simulator,http',
            'environment' => 'required|in:sandbox,live',
            'base_url' => 'nullable|url|max:500',
            'services' => 'required|array|min:1',
            'services.*' => 'in:imps,neft,rtgs',
        ]);

        if ($this->driver === 'http' && $this->base_url === '') {
            $this->addError('base_url', 'Bank API base URL is required for HTTP integration.');

            return;
        }

        if ($this->base_url !== '') {
            try {
                OutboundUrl::assertSafe($this->base_url, app()->environment('local'));
            } catch (InvalidArgumentException $e) {
                $this->addError('base_url', $e->getMessage());

                return;
            }
        }

        $payload = [
            'name' => $this->name,
            'code' => $this->code,
            'driver' => $this->driver,
            'environment' => $this->environment,
            'base_url' => $this->base_url ?: null,
            'services' => array_values($this->services),
            'is_active' => $this->is_active,
            'is_default' => $this->is_default,
        ];

        $isUpdate = $this->editingId !== null;

        if ($this->editingId) {
            $bank = Bank::findOrFail($this->editingId);
            if ($this->username !== '') {
                $payload['username'] = $this->username;
            }
            if ($this->password !== '') {
                $payload['password'] = $this->password;
            }
            if ($this->api_key !== '') {
                $payload['api_key'] = $this->api_key;
            }
            if ($this->api_secret !== '') {
                $payload['api_secret'] = $this->api_secret;
            }
            $bank->update($payload);
        } else {
            $bank = Bank::create($payload + [
                'username' => $this->username ?: null,
                'password' => $this->password ?: null,
                'api_key' => $this->api_key ?: null,
                'api_secret' => $this->api_secret ?: null,
            ]);
        }

        if ($bank->is_default) {
            Bank::query()->where('id', '!=', $bank->id)->update(['is_default' => false]);
        }

        if (! $isUpdate) {
            $this->seedCatalog($bank);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', $isUpdate
            ? 'Bank updated.'
            : 'Bank added with 12 API templates. Open Bank APIs to map each bank path, then assign vendors.');
    }

    public function testConnection(int $id): void
    {
        $bank = Bank::findOrFail($id);
        $result = app(BankGatewayManager::class)->for($bank)->testConnection($bank);
        $bank->update([
            'last_tested_at' => now(),
            'last_test_status' => $result->status,
            'last_test_message' => $result->message,
        ]);
        session()->flash('success', $result->message ?: 'Test completed.');
    }

    public function toggleActive(int $id): void
    {
        $bank = Bank::findOrFail($id);
        $bank->update(['is_active' => ! $bank->is_active]);
    }

    public function openAssign(int $id): void
    {
        $bank = Bank::with('vendors')->findOrFail($id);
        $this->assignBankId = $bank->id;
        $this->assignedVendorIds = $bank->vendors->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->assignSearch = '';
        $this->showAssignModal = true;
    }

    public function saveAssignments(): void
    {
        $bank = Bank::findOrFail($this->assignBankId);
        $ids = collect($this->assignedVendorIds)->map(fn ($id) => (int) $id)->filter()->unique()->all();
        $sync = [];
        foreach ($ids as $vendorId) {
            $sync[$vendorId] = ['is_enabled' => true];
        }
        $bank->vendors()->sync($sync);

        if ($ids !== []) {
            Vendor::query()->whereIn('id', $ids)->update(['api_enabled' => true]);
        }

        $this->showAssignModal = false;
        session()->flash('success', 'Bank API assigned to selected vendors. They can now see endpoints in their developer panel.');
    }

    public function openApis(int $id): void
    {
        Bank::findOrFail($id);
        $this->apisBankId = $id;
        $this->showEndpointForm = false;
        $this->showApisModal = true;
    }

    public function seedMissingApis(): void
    {
        $bank = Bank::findOrFail($this->apisBankId);
        $this->seedCatalog($bank);
        session()->flash('success', 'Missing API templates were added.');
    }

    public function openCreateEndpoint(): void
    {
        $this->resetEndpointForm();
        $this->showEndpointForm = true;
    }

    public function openEditEndpoint(int $id): void
    {
        $endpoint = BankApiEndpoint::where('bank_id', $this->apisBankId)->findOrFail($id);
        $this->editingEndpointId = $endpoint->id;
        $this->endpointName = $endpoint->name;
        $this->endpointSlug = $endpoint->slug;
        $this->endpointMethod = $endpoint->method;
        $this->endpointBankPath = $endpoint->bank_path ?? '';
        $this->endpointDescription = $endpoint->description ?? '';
        $this->requestParams = $endpoint->request_params ?: [];
        $this->responseParams = $endpoint->response_params ?: [];
        $this->sampleRequestJson = json_encode($endpoint->sample_request ?: new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->sampleResponseJson = json_encode($endpoint->sample_response ?: new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->endpointActive = $endpoint->is_active;
        $this->showEndpointForm = true;
    }

    public function addRequestParam(): void
    {
        $this->requestParams[] = ['name' => '', 'type' => 'string', 'required' => true, 'description' => ''];
    }

    public function removeRequestParam(int $index): void
    {
        unset($this->requestParams[$index]);
        $this->requestParams = array_values($this->requestParams);
    }

    public function addResponseParam(): void
    {
        $this->responseParams[] = ['name' => '', 'type' => 'string', 'required' => true, 'description' => ''];
    }

    public function removeResponseParam(int $index): void
    {
        unset($this->responseParams[$index]);
        $this->responseParams = array_values($this->responseParams);
    }

    public function saveEndpoint(): void
    {
        $this->endpointSlug = Str::slug($this->endpointSlug ?: $this->endpointName);

        $this->validate([
            'endpointName' => 'required|string|max:120',
            'endpointSlug' => [
                'required',
                'string',
                'max:80',
                Rule::unique('bank_api_endpoints', 'slug')
                    ->where(fn ($q) => $q->where('bank_id', $this->apisBankId))
                    ->ignore($this->editingEndpointId),
            ],
            'endpointMethod' => 'required|in:GET,POST,PUT,PATCH,DELETE',
            'endpointBankPath' => 'nullable|string|max:255',
            'endpointDescription' => 'nullable|string|max:1000',
            'sampleRequestJson' => 'nullable|string',
            'sampleResponseJson' => 'nullable|string',
        ]);

        if ($this->endpointBankPath !== '') {
            try {
                OutboundUrl::join('https://example.invalid', $this->endpointBankPath);
            } catch (InvalidArgumentException $e) {
                $this->addError('endpointBankPath', $e->getMessage());

                return;
            }
        }

        $requestJson = json_decode($this->sampleRequestJson !== '' ? $this->sampleRequestJson : '{}', true);
        $responseJson = json_decode($this->sampleResponseJson !== '' ? $this->sampleResponseJson : '{}', true);
        if ($requestJson === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->addError('sampleRequestJson', 'Sample request must be valid JSON.');

            return;
        }
        if ($responseJson === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->addError('sampleResponseJson', 'Sample response must be valid JSON.');

            return;
        }

        $payload = [
            'name' => $this->endpointName,
            'slug' => $this->endpointSlug,
            'method' => $this->endpointMethod,
            'bank_path' => $this->endpointBankPath ?: null,
            'description' => $this->endpointDescription ?: null,
            'request_params' => array_values(array_filter($this->requestParams, fn ($row) => trim((string) ($row['name'] ?? '')) !== '')),
            'response_params' => array_values(array_filter($this->responseParams, fn ($row) => trim((string) ($row['name'] ?? '')) !== '')),
            'sample_request' => $requestJson,
            'sample_response' => $responseJson,
            'is_active' => $this->endpointActive,
        ];

        if ($this->editingEndpointId) {
            $endpoint = BankApiEndpoint::where('bank_id', $this->apisBankId)->findOrFail($this->editingEndpointId);
            $endpoint->update($payload);
        } else {
            $max = (int) BankApiEndpoint::where('bank_id', $this->apisBankId)->max('sort_order');
            BankApiEndpoint::create($payload + [
                'bank_id' => $this->apisBankId,
                'sort_order' => $max + 1,
            ]);
        }

        $this->showEndpointForm = false;
        $this->resetEndpointForm();
        session()->flash('success', 'Bank API saved. Vendor docs will show FinPay path + request/response.');
    }

    public function deleteEndpoint(int $id): void
    {
        BankApiEndpoint::where('bank_id', $this->apisBankId)->where('id', $id)->delete();
    }

    public function toggleEndpoint(int $id): void
    {
        $endpoint = BankApiEndpoint::where('bank_id', $this->apisBankId)->findOrFail($id);
        $endpoint->update(['is_active' => ! $endpoint->is_active]);
    }

    private function seedCatalog(Bank $bank): void
    {
        $existing = $bank->apiEndpoints()->pluck('slug')->all();
        foreach (BankApiCatalog::templates() as $i => $tpl) {
            if (in_array($tpl['slug'], $existing, true)) {
                continue;
            }
            $bank->apiEndpoints()->create([
                'name' => $tpl['name'],
                'slug' => $tpl['slug'],
                'method' => $tpl['method'],
                'bank_path' => $tpl['bank_path'],
                'description' => $tpl['description'],
                'request_params' => $tpl['request_params'],
                'response_params' => $tpl['response_params'],
                'sample_request' => $tpl['sample_request'],
                'sample_response' => $tpl['sample_response'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
    }

    private function resetEndpointForm(): void
    {
        $this->reset([
            'editingEndpointId', 'endpointName', 'endpointSlug', 'endpointBankPath',
            'endpointDescription', 'sampleRequestJson', 'sampleResponseJson',
        ]);
        $this->endpointMethod = 'POST';
        $this->requestParams = [];
        $this->responseParams = [];
        $this->sampleRequestJson = '{}';
        $this->sampleResponseJson = '{}';
        $this->endpointActive = true;
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'name', 'code', 'base_url', 'username', 'password',
            'api_key', 'api_secret', 'testMessage',
        ]);
        $this->driver = 'http';
        $this->environment = 'sandbox';
        $this->services = ['imps', 'neft', 'rtgs'];
        $this->is_active = true;
        $this->is_default = false;
        $this->resetValidation();
    }

    public function render()
    {
        $banks = Bank::query()
            ->withCount(['vendors', 'apiEndpoints'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($inner) {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->paginate(12);

        $apiEndpoints = collect();
        $apisBank = null;
        if ($this->showApisModal && $this->apisBankId) {
            $apisBank = Bank::find($this->apisBankId);
            $apiEndpoints = $apisBank
                ? $apisBank->apiEndpoints()->get()
                : collect();
        }

        $vendors = collect();
        if ($this->showAssignModal) {
            $vendors = Vendor::query()
                ->when($this->assignSearch !== '', function ($q) {
                    $q->where('business_name', 'like', '%'.$this->assignSearch.'%')
                        ->orWhere('vendor_code', 'like', '%'.$this->assignSearch.'%');
                })
                ->orderBy('business_name')
                ->limit(50)
                ->get();
        }

        return view('livewire.admin.banks', compact('banks', 'vendors', 'apiEndpoints', 'apisBank'))
            ->layout('layouts.admin', ['title' => 'Banks']);
    }
}
