<?php
namespace App\Livewire\Vendor;

use App\Models\ApiCredential;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class Developer extends Component {
    public bool   $showSecret   = false;
    public string $webhookUrl   = '';
    public string $ipWhitelist  = '';
    public bool   $saved        = false;
    public ?ApiCredential $creds = null;

    public function mount(): void {
        $vendor      = Auth::guard('vendor')->user();
        $this->creds = $vendor->apiCredential;
        if ($this->creds) {
            $this->webhookUrl  = $this->creds->webhook_url ?? '';
            $this->ipWhitelist = implode("\n", $this->creds->ip_whitelist ?? []);
        }
    }

    public function regenerateApiKey(): void {
        $vendor = Auth::guard('vendor')->user();
        $creds  = $vendor->apiCredential ?? new ApiCredential(['vendor_id' => $vendor->id]);
        $creds->api_key = 'pk_'.Str::random(32);
        if (!$creds->secret_key) {
            $creds->secret_key = 'sk_'.Str::random(48);
        }
        $creds->save();
        $this->creds = $creds;
        $this->dispatch('notify', message: 'API key regenerated.');
    }

    public function regenerateSecret(): void {
        if (!$this->creds) return;
        $this->creds->secret_key = 'sk_'.Str::random(48);
        $this->creds->save();
        $this->dispatch('notify', message: 'Secret key regenerated.');
    }

    public function saveSettings(): void {
        $this->validate([
            'webhookUrl'  => 'nullable|url|max:500',
            'ipWhitelist' => 'nullable|string',
        ]);
        $vendor = Auth::guard('vendor')->user();
        $ips    = array_filter(array_map('trim', explode("\n", $this->ipWhitelist)));
        $creds  = $vendor->apiCredential ?? ApiCredential::create([
            'vendor_id'  => $vendor->id,
            'api_key'    => 'pk_'.Str::random(32),
            'secret_key' => 'sk_'.Str::random(48),
        ]);
        $creds->update(['webhook_url' => $this->webhookUrl, 'ip_whitelist' => array_values($ips)]);
        $this->creds = $creds->fresh();
        $this->saved = true;
    }

    public function render() {
        $logs = $this->creds
            ? Auth::guard('vendor')->user()->webhookLogs()->latest()->limit(10)->get()
            : collect();
        return view('livewire.vendor.developer', compact('logs'))
            ->layout('layouts.vendor', ['title' => 'Developer']);
    }
}
