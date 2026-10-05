<?php

namespace App\Livewire\Vendor;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    // ── Wallet ────────────────────────────────────────────────────────────────
    public string $availableBalance = '0.00';
    public string $holdBalance      = '0.00';

    // ── Today stats ───────────────────────────────────────────────────────────
    public int    $todayTotal       = 0;
    public int    $todaySuccess     = 0;
    public int    $todayFailed      = 0;
    public int    $todayPending     = 0;
    public string $todayVolume      = '0.00';

    // ── Monthly stats ─────────────────────────────────────────────────────────
    public int    $monthTotal       = 0;
    public string $monthVolume      = '0.00';
    public string $successRate      = '0';
    public string $failedRate       = '0';
    public string $pendingRate      = '0';

    // ── Recent transactions ────────────────────────────────────────────────
    public array  $recentTransactions = [];

    // ── Vendor info ────────────────────────────────────────────────────────
    public string $greeting         = '';
    public string $vendorCode       = '';
    public string $kycStatus        = '';

    public function mount(): void
    {
        $vendor = Auth::guard('vendor')->user();

        $this->vendorCode = $vendor->vendor_code;
        $this->kycStatus  = $vendor->kyc_status;
        $this->greeting   = $this->buildGreeting($vendor->business_name);

        $this->loadWallet($vendor);
        $this->loadTodayStats($vendor);
        $this->loadMonthStats($vendor);
        $this->loadRecentTransactions($vendor);
    }

    // ── Greeting ──────────────────────────────────────────────────────────────
    private function buildGreeting(string $name): string
    {
        $hour = (int) now()->format('H');
        $time = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default    => 'Good evening',
        };
        return "{$time}, {$name}";
    }

    // ── Wallet ────────────────────────────────────────────────────────────────
    private function loadWallet($vendor): void
    {
        $wallet = $vendor->wallet;
        if ($wallet) {
            $this->availableBalance = number_format((float) $wallet->balance, 2);
            $this->holdBalance      = number_format((float) $wallet->hold_balance, 2);
        }
    }

    // ── Today Stats ───────────────────────────────────────────────────────────
    private function loadTodayStats($vendor): void
    {
        $base = $vendor->transactions()->whereDate('created_at', today());

        $this->todayTotal   = (clone $base)->count();
        $this->todaySuccess = (clone $base)->where('status', 'success')->count();
        $this->todayFailed  = (clone $base)->where('status', 'failed')->count();
        $this->todayPending = (clone $base)->where('status', 'pending')->count();
        $this->todayVolume  = number_format(
            (float) (clone $base)->where('status', 'success')->sum('amount'), 2
        );
    }

    // ── Month Stats ───────────────────────────────────────────────────────────
    private function loadMonthStats($vendor): void
    {
        $base = $vendor->transactions()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at',  now()->year);

        $this->monthTotal  = (clone $base)->count();
        $this->monthVolume = number_format(
            (float) (clone $base)->where('status', 'success')->sum('amount'), 2
        );

        $success = (clone $base)->where('status', 'success')->count();
        $failed  = (clone $base)->where('status', 'failed')->count();
        $pending = (clone $base)->where('status', 'pending')->count();

        $total = max($this->monthTotal, 1);
        $this->successRate = number_format($success / $total * 100, 1);
        $this->failedRate  = number_format($failed  / $total * 100, 1);
        $this->pendingRate = number_format($pending / $total * 100, 1);
    }

    // ── Recent Transactions ───────────────────────────────────────────────────
    private function loadRecentTransactions($vendor): void
    {
        $this->recentTransactions = $vendor->transactions()
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($tx) => [
                'id'               => $tx->id,
                'reference'        => $tx->reference,
                'raw_type'         => $tx->type,
                'beneficiary_name' => $tx->beneficiary_name ?? '—',
                'amount'           => number_format((float) $tx->amount, 2),
                'type'             => ucfirst($tx->type),
                'service'          => strtoupper($tx->service),
                'status'           => $tx->status,
                'date'             => $tx->created_at->format('d M, h:i A'),
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.vendor.dashboard', [
            'activity' => $this->activity(Auth::guard('vendor')->user()),
        ])->layout('layouts.vendor');
    }

    private function activity($vendor): array
    {
        $start = now()->subDays(6)->startOfDay();
        $end = now()->endOfDay();
        $rows = $vendor->transactions()
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at', 'status']);

        $grouped = [];
        foreach ($rows as $row) {
            $day = $row->created_at->format('Y-m-d');
            $status = (string) $row->status;
            $grouped[$day][$status] = ($grouped[$day][$status] ?? 0) + 1;
        }

        $days = [];
        $max = 0;
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->toDateString();
            $bucket = $grouped[$key] ?? [];
            $success = (int) ($bucket['success'] ?? 0);
            $failed = (int) ($bucket['failed'] ?? 0);
            $pending = (int) ($bucket['pending'] ?? 0);
            unset($bucket['success'], $bucket['failed'], $bucket['pending']);
            $other = array_sum($bucket);
            $total = $success + $failed + $pending + $other;
            $max = max($max, $total);
            $days[] = [
                'label' => $date->format('d M'),
                'title' => $date->format('d M Y'),
                'success' => $success,
                'failed' => $failed,
                'pending' => $pending,
                'other' => $other,
                'total' => $total,
            ];
        }

        return [
            'from' => $start->format('d M Y, h:i A'),
            'to' => $end->format('d M Y, h:i A'),
            'max' => $max,
            'days' => $days,
            'count' => $rows->count(),
        ];
    }
}
