<?php

namespace App\Livewire\Admin;

use App\Support\SampleWalletReport;
use App\Support\SimpleXlsx;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DailyBalance extends Component
{
    public string $vendor = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $onDate = '';

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('reports')) {
            abort(403);
        }

        $this->thisMonth();
    }

    public function thisMonth(): void
    {
        $this->onDate = '';
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function previousMonth(): void
    {
        $this->onDate = '';
        $month = now()->subMonthNoOverflow();
        $this->dateFrom = $month->copy()->startOfMonth()->toDateString();
        $this->dateTo = $month->copy()->endOfMonth()->toDateString();
    }

    public function yesterday(): void
    {
        $day = now()->subDay()->toDateString();
        $this->onDate = $day;
        $this->dateFrom = $day;
        $this->dateTo = $day;
    }

    public function updatedOnDate(): void
    {
        if ($this->onDate === '') {
            return;
        }

        $this->dateFrom = $this->onDate;
        $this->dateTo = $this->onDate;
    }

    public function exportExcel(): StreamedResponse
    {
        $report = $this->report();
        $rows = [['Date', 'Opening', 'Credit', 'Debit', 'Closing']];

        foreach ($report['days'] as $day) {
            $rows[] = [$day['date'], $day['opening'], $day['credit'], $day['debit'], $day['closing']];
        }

        $rows[] = ['Period', $report['opening'], $report['credit'], $report['debit'], $report['closing']];

        return SimpleXlsx::download('daily-balance.xlsx', [
            'Daily balance' => $rows,
        ]);
    }

    public function render()
    {
        return view('livewire.admin.daily-balance', [
            'vendors' => SampleWalletReport::vendors(),
            'report' => $this->report(),
            'singleDay' => $this->dateFrom !== '' && $this->dateFrom === $this->dateTo,
        ])->layout('layouts.admin', ['title' => 'Daily balance']);
    }

    private function report(): array
    {
        [$from, $to] = $this->range();

        return SampleWalletReport::daily($this->vendor !== '' ? $this->vendor : null, $from, $to);
    }

    private function range(): array
    {
        $from = $this->dateFrom !== '' ? Carbon::parse($this->dateFrom)->startOfDay() : now()->startOfMonth();
        $to = $this->dateTo !== '' ? Carbon::parse($this->dateTo)->startOfDay() : now()->startOfDay();

        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        if ($from->diffInDays($to) > 62) {
            $to = $from->copy()->addDays(62);
        }

        return [$from->toDateString(), $to->toDateString()];
    }
}
