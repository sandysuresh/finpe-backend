<?php

namespace App\Livewire\Vendor;

use App\Models\ApiCredential;
use App\Support\OutboundUrl;
use App\Support\VendorApiSecurity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Livewire\Component;

class Developer extends Component
{
    public string $webhookUrl = '';

    public string $ipWhitelist = '';

    public bool $saved = false;

    public function mount(): void
    {
        $creds = Auth::guard('vendor')->user()?->apiCredential;
        if ($creds) {
            $this->webhookUrl = $creds->webhook_url ?? '';
            $this->ipWhitelist = implode("\n", $creds->ip_whitelist ?? []);
        }
    }

    public function saveSettings(): void
    {
        $this->validate([
            'webhookUrl' => 'nullable|url|max:500',
            'ipWhitelist' => 'required|string',
        ]);

        if ($this->webhookUrl !== '') {
            try {
                OutboundUrl::assertSafe($this->webhookUrl, false);
            } catch (InvalidArgumentException $e) {
                $this->addError('webhookUrl', $e->getMessage());

                return;
            }
        }

        $ips = VendorApiSecurity::parseWhitelist($this->ipWhitelist);

        if ($ips === []) {
            $this->addError('ipWhitelist', 'At least one IP address is required for API access.');

            return;
        }

        foreach ($ips as $ip) {
            if (! VendorApiSecurity::isValidWhitelistEntry($ip)) {
                $this->addError('ipWhitelist', "Invalid IP or CIDR: {$ip}");

                return;
            }
        }

        $vendor = Auth::guard('vendor')->user();
        $creds = $vendor->apiCredential ?? ApiCredential::create([
            'vendor_id' => $vendor->id,
            'api_key' => 'pk_'.Str::random(32),
            'secret_key' => 'sk_'.Str::random(48),
        ]);
        $creds->update([
            'webhook_url' => $this->webhookUrl ?: null,
            'ip_whitelist' => array_values($ips),
        ]);
        $this->saved = true;
    }

    public function render()
    {
        $vendor = Auth::guard('vendor')->user()->load([
            'assignedBanks.apiEndpoints' => fn ($q) => $q->where('is_active', true),
        ]);

        return view('livewire.vendor.developer', [
            'assignedBanks' => $vendor->assignedBanks,
            'apiBase' => rtrim(config('app.url'), '/'),
        ])->layout('layouts.vendor', ['title' => 'API Documentation']);
    }
}
