<?php

namespace App\Support;

use App\Models\AlliancePePayout;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\WalletLedger;
use App\Services\Payout\PayoutProviderRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WalletLedgerReport
{
    public function __construct(private PayoutProviderRegistry $providers) {}

    /**
     * @param  array{vendor_id?: int|string|null, date_from?: string, date_to?: string, provider?: string, entry?: string, status?: string, search?: string}  $input
     * @return array<string, mixed>
     */
    public function statement(array $input): array
    {
        $filters = $this->filters($input);
        $scope = $this->scope($filters);
        $opening = $this->opening($filters, $scope);
        $period = $this->period($filters);
        $credit = round((float) (clone $period)->where('wallet_ledger.type', 'credit')->sum('wallet_ledger.amount'), 2);
        $debit = round((float) (clone $period)->where('wallet_ledger.type', 'debit')->sum('wallet_ledger.amount'), 2);
        $refunds = round((float) $this->refunds(clone $period)->sum('wallet_ledger.amount'), 2);
        $count = (clone $period)->count();
        $closing = round($opening + $credit - $debit, 2);

        return [
            'filters' => $filters,
            'scope' => $scope,
            'labels' => $this->labels($scope),
            'note' => $this->note($filters, $scope),
            'summary' => [
                'opening' => $opening,
                'credit' => $credit,
                'debit' => $debit,
                'refunds' => $refunds,
                'count' => $count,
                'closing' => $closing,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function lines(array $filters, float $opening): Collection
    {
        $running = $opening;
        $rows = $this->period($filters)
            ->orderBy('wallet_ledger.created_at')
            ->orderBy('wallet_ledger.id')
            ->get();

        return $rows->map(function (WalletLedger $row) use (&$running) {
            $running = round($running + $this->signed($row), 2);

            return $this->present($row, $running);
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: float, 1: Collection<int, array<string, mixed>>}
     */
    public function page(array $filters, float $opening, int $page, int $perPage): array
    {
        $period = $this->period($filters)
            ->orderBy('wallet_ledger.created_at')
            ->orderBy('wallet_ledger.id');
        $paginator = $period->paginate($perPage, ['*'], 'ledgerPage', $page);
        $first = $paginator->first();
        $running = $opening;

        if ($first instanceof WalletLedger) {
            $before = $this->period($filters)
                ->where(function (Builder $query) use ($first) {
                    $query->where('wallet_ledger.created_at', '<', $first->created_at)
                        ->orWhere(function (Builder $same) use ($first) {
                            $same->where('wallet_ledger.created_at', $first->created_at)
                                ->where('wallet_ledger.id', '<', $first->id);
                        });
                });
            $running = round($opening + $this->signedSum($before), 2);
        }

        $lines = $paginator->getCollection()->map(function ($row) use (&$running) {
            $running = round($running + $this->signed($row), 2);

            return $this->present($row, $running);
        });
        $paginator->setCollection($lines);

        return [$running, $paginator];
    }

    /**
     * @return array{heading: string, opening: string, closing: string}
     */
    public function labels(string $scope): array
    {
        if ($scope === 'provider') {
            return [
                'heading' => 'Provider-linked ledger. These figures are not the full wallet balance.',
                'opening' => 'Opening Provider-Linked Balance',
                'closing' => 'Closing Provider-Linked Balance',
            ];
        }

        if ($scope === 'filtered') {
            return [
                'heading' => 'Filtered ledger balances. These figures are not the full wallet balance.',
                'opening' => 'Opening Filtered Balance',
                'closing' => 'Closing Filtered Balance',
            ];
        }

        return [
            'heading' => 'Wallet ledger balances. Closing wallet balance = opening wallet balance + credits − debits.',
            'opening' => 'Opening Wallet Balance',
            'closing' => 'Closing Wallet Balance',
        ];
    }

    /**
     * @param  array<string, mixed>  $statement
     * @return list<list<mixed>>
     */
    public function summaryRows(array $statement, ?string $vendorLine = null): array
    {
        $labels = $statement['labels'];
        $summary = $statement['summary'];
        $rows = [];
        if ($vendorLine !== null) {
            $rows[] = ['Vendor', $vendorLine];
        }
        $rows[] = [$labels['heading']];
        $rows[] = [$statement['note']];
        $rows[] = [];
        $rows[] = [$labels['opening'], $summary['opening']];
        $rows[] = ['Total credit', $summary['credit']];
        $rows[] = ['Total debit', $summary['debit']];
        $rows[] = ['Refunds', $summary['refunds']];
        $rows[] = ['Transaction count', $summary['count']];
        $rows[] = [$labels['closing'], $summary['closing']];

        return $rows;
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    public function providerOptions(): array
    {
        $rows = array_map(
            fn (array $row) => ['code' => $row['code'], 'name' => $row['name']],
            $this->providers->catalog()
        );
        if (! collect($rows)->contains(fn (array $row) => $row['code'] === CommissionProviders::ALLIANCEPE)) {
            $rows[] = ['code' => CommissionProviders::ALLIANCEPE, 'name' => CommissionProviders::name(CommissionProviders::ALLIANCEPE)];
        }

        return $rows;
    }

    /**
     * @return Collection<int, Vendor>
     */
    public function vendors(): Collection
    {
        return Vendor::query()->orderBy('business_name')->get(['id', 'vendor_code', 'business_name']);
    }

    /**
     * @return list<string>
     */
    public function statuses(): array
    {
        $statuses = Transaction::query()
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->all();

        return array_values(array_unique(array_merge($statuses, AlliancePePayout::query()
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->pluck('status')
            ->all())));
    }

    /**
     * @return array<string, string>
     */
    public function entryOptions(): array
    {
        $options = [
            'credit' => 'Credit',
            'debit' => 'Debit',
            'refund' => 'Refund',
            'topup' => 'Top-up',
            'payout' => 'Payout',
            'source:manual' => 'Manual',
            'source:settlement' => 'Settlement',
            'source:transaction' => 'Transaction',
        ];

        $known = ['topup', 'manual', 'settlement', 'transaction', 'refund'];
        $extra = WalletLedger::query()->distinct()->orderBy('source')->pluck('source');
        foreach ($extra as $source) {
            $source = (string) $source;
            if ($source === '' || in_array($source, $known, true)) {
                continue;
            }
            $options['source:'.$source] = ucfirst(str_replace('_', ' ', $source));
        }

        return $options;
    }

    /**
     * @param  array{vendor_id?: int|string|null, date_from?: string, date_to?: string, provider?: string, entry?: string, status?: string, search?: string}  $input
     * @return array{vendor_id: int|null, date_from: string, date_to: string, provider: string, entry: string, status: string, search: string}
     */
    public function filters(array $input): array
    {
        $vendorId = isset($input['vendor_id']) && $input['vendor_id'] !== '' ? (int) $input['vendor_id'] : null;
        $provider = strtolower(trim((string) ($input['provider'] ?? '')));
        $allowedProviders = array_column($this->providerOptions(), 'code');
        if (! in_array($provider, $allowedProviders, true)) {
            $provider = '';
        }

        $entry = (string) ($input['entry'] ?? '');
        if (! array_key_exists($entry, $this->entryOptions())) {
            $entry = '';
        }

        return [
            'vendor_id' => $vendorId,
            'date_from' => $this->date((string) ($input['date_from'] ?? '')),
            'date_to' => $this->date((string) ($input['date_to'] ?? '')),
            'provider' => $provider,
            'entry' => $entry,
            'status' => trim((string) ($input['status'] ?? '')),
            'search' => trim((string) ($input['search'] ?? '')),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function opening(array $filters, string $scope): float
    {
        if ($scope !== 'wallet') {
            if ($filters['date_from'] === '') {
                return 0.0;
            }

            return round($this->signedSum(
                $this->base($filters)->whereDate('wallet_ledger.created_at', '<', $filters['date_from'])
            ), 2);
        }

        $vendorId = $filters['vendor_id'];
        $ranked = WalletLedger::query()
            ->when($vendorId, fn (Builder $query) => $query->where('vendor_id', $vendorId))
            ->when($filters['date_from'] !== '', fn (Builder $query) => $query->whereDate('created_at', '<', $filters['date_from']));

        if ($filters['date_from'] === '') {
            $ranked->select('vendor_id', 'balance_before')
                ->selectRaw('ROW_NUMBER() OVER (PARTITION BY vendor_id ORDER BY created_at ASC, id ASC) as rn');
            $column = 'balance_before';
        } else {
            $ranked->select('vendor_id', 'balance_after')
                ->selectRaw('ROW_NUMBER() OVER (PARTITION BY vendor_id ORDER BY created_at DESC, id DESC) as rn');
            $column = 'balance_after';
        }

        $total = DB::query()->fromSub($ranked->toBase(), 'ranked')->where('rn', 1)->sum($column);

        return round((float) $total, 2);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function period(array $filters): Builder
    {
        return $this->base($filters)
            ->when($filters['date_from'] !== '', fn (Builder $query) => $query->whereDate('wallet_ledger.created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn (Builder $query) => $query->whereDate('wallet_ledger.created_at', '<=', $filters['date_to']));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function base(array $filters): Builder
    {
        $query = WalletLedger::query()
            ->select('wallet_ledger.*')
            ->with('vendor:id,business_name,vendor_code')
            ->addSelect([
                'txn_status' => $this->transactionColumn('status'),
                'txn_provider' => $this->transactionColumn('payout_provider'),
                'txn_type' => $this->transactionColumn('type'),
                'txn_reference' => $this->transactionColumn('reference'),
            ])
            ->when($filters['vendor_id'], fn (Builder $inner) => $inner->where('wallet_ledger.vendor_id', $filters['vendor_id']))
            ->when($filters['provider'] !== '', function (Builder $inner) use ($filters) {
                $provider = strtolower((string) $filters['provider']);
                if ($provider === CommissionProviders::ALLIANCEPE) {
                    $inner->whereExists(function ($sub) {
                        $sub->selectRaw('1')
                            ->from('alliancepe_payouts')
                            ->whereColumn('alliancepe_payouts.vendor_id', 'wallet_ledger.vendor_id')
                            ->whereColumn('alliancepe_payouts.merchant_order_id', 'wallet_ledger.reference');
                    });

                    return;
                }
                $inner->whereExists(function ($sub) use ($provider) {
                    $this->matchTransaction($sub)->where('transactions.payout_provider', $provider);
                });
            })
            ->when($filters['status'] !== '', function (Builder $inner) use ($filters) {
                app(VisibleTransactions::class)->constrainLedger($inner, (string) $filters['status'], '');
            })
            ->when($filters['search'] !== '', function (Builder $inner) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $inner->where(function (Builder $match) use ($term) {
                    $match->where('wallet_ledger.reference', 'like', $term)
                        ->orWhere('wallet_ledger.description', 'like', $term)
                        ->orWhereHas('vendor', function (Builder $vendor) use ($term) {
                            $vendor->where('business_name', 'like', $term)
                                ->orWhere('vendor_code', 'like', $term);
                        })
                        ->orWhereExists(function ($sub) use ($term) {
                            $this->matchTransaction($sub)->where(function ($txn) use ($term) {
                                $txn->where('transactions.reference', 'like', $term)
                                    ->orWhere('transactions.merchant_ref', 'like', $term);
                            });
                        });
                });
            });

        $this->applyEntry($query, (string) $filters['entry']);

        return $query;
    }

    private function applyEntry(Builder $query, string $entry): void
    {
        if ($entry === 'credit' || $entry === 'debit') {
            $query->where('wallet_ledger.type', $entry);

            return;
        }

        if ($entry === 'refund') {
            $this->refunds($query);

            return;
        }

        if ($entry === 'topup') {
            $query->where('wallet_ledger.source', 'topup');

            return;
        }

        if ($entry === 'payout') {
            $query->where('wallet_ledger.type', 'debit')
                ->whereExists(function ($sub) {
                    $this->matchTransaction($sub)->where('transactions.type', 'payout');
                });

            return;
        }

        if (str_starts_with($entry, 'source:')) {
            $query->where('wallet_ledger.source', substr($entry, 7));
        }
    }

    private function refunds(Builder $query): Builder
    {
        return $query->where(function (Builder $refund) {
            $refund->where('wallet_ledger.source', 'refund')
                ->orWhere(function (Builder $payoutRefund) {
                    $payoutRefund->where('wallet_ledger.type', 'credit')
                        ->where('wallet_ledger.description', 'like', 'Payout refund%');
                });
        });
    }

    private function transactionColumn(string $column): \Illuminate\Contracts\Database\Query\Expression
    {
        $column = in_array($column, ['status', 'payout_provider', 'type', 'reference'], true) ? $column : 'status';
        $alliance = match ($column) {
            'payout_provider' => "'".CommissionProviders::ALLIANCEPE."'",
            'type' => "'payout'",
            'reference' => 'alliancepe_payouts.merchant_order_id',
            default => 'alliancepe_payouts.status',
        };

        return DB::raw("COALESCE((SELECT transactions.{$column} FROM transactions WHERE transactions.vendor_id = wallet_ledger.vendor_id AND transactions.reference = wallet_ledger.reference ORDER BY transactions.id LIMIT 1), (SELECT {$alliance} FROM alliancepe_payouts WHERE alliancepe_payouts.vendor_id = wallet_ledger.vendor_id AND alliancepe_payouts.merchant_order_id = wallet_ledger.reference ORDER BY alliancepe_payouts.id LIMIT 1))");
    }

    private function matchTransaction($sub)
    {
        return $sub->selectRaw('1')
            ->from('transactions')
            ->whereColumn('transactions.vendor_id', 'wallet_ledger.vendor_id')
            ->whereColumn('transactions.reference', 'wallet_ledger.reference');
    }

    private function signed(WalletLedger $row): float
    {
        $amount = round((float) $row->amount, 2);

        return $row->type === 'credit' ? $amount : round(-1 * $amount, 2);
    }

    private function signedSum(Builder $query): float
    {
        $credits = (float) (clone $query)->where('wallet_ledger.type', 'credit')->sum('wallet_ledger.amount');
        $debits = (float) (clone $query)->where('wallet_ledger.type', 'debit')->sum('wallet_ledger.amount');

        return round($credits - $debits, 2);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(WalletLedger $row, float $running): array
    {
        $provider = trim((string) ($row->txn_provider ?? ''));

        return [
            'at' => $row->created_at,
            'vendor_name' => $row->vendor->business_name ?? '',
            'vendor_code' => $row->vendor->vendor_code ?? '',
            'vendor_id' => $row->vendor_id,
            'reference' => (string) ($row->reference ?? ''),
            'entry' => $this->entryLabel($row),
            'source' => (string) $row->source,
            'provider' => $provider === '' ? '' : $this->providers->displayName($provider),
            'description' => (string) ($row->description ?? ''),
            'credit' => $row->type === 'credit' ? round((float) $row->amount, 2) : 0.0,
            'debit' => $row->type === 'debit' ? round((float) $row->amount, 2) : 0.0,
            'running' => $running,
            'txn_status' => (string) ($row->txn_status ?? ''),
            'txn_reference' => (string) ($row->txn_reference ?? ''),
        ];
    }

    private function entryLabel(WalletLedger $row): string
    {
        if ($this->isRefund($row)) {
            return 'Refund';
        }
        if ($row->source === 'topup') {
            return 'Top-up';
        }
        if ($row->type === 'debit' && ($row->txn_type ?? '') === 'payout') {
            return 'Payout';
        }

        return ucfirst((string) $row->type).' / '.ucfirst((string) $row->source);
    }

    private function isRefund(WalletLedger $row): bool
    {
        return $row->source === 'refund'
            || ($row->type === 'credit' && str_starts_with((string) $row->description, 'Payout refund'));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function scope(array $filters): string
    {
        if ($filters['entry'] !== '' || $filters['status'] !== '' || $filters['search'] !== '') {
            return 'filtered';
        }

        if ($filters['provider'] !== '') {
            return 'provider';
        }

        return 'wallet';
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function note(array $filters, string $scope): string
    {
        if ($scope === 'provider') {
            $name = $this->providers->displayName($filters['provider']);

            return $name.' opening and closing are provider-linked balances, not the full wallet balance. Ledger entries without a payout provider, including provider-less top-ups, are excluded.';
        }

        if ($scope === 'filtered') {
            $note = 'Opening and closing are filtered balances, not the full wallet balance. Only ledger rows matching the selected type, status, or search are included.';
            if ($filters['provider'] !== '') {
                $note .= ' '.$this->providers->displayName($filters['provider']).' is also applied. Ledger entries without that payout provider, including provider-less top-ups, are excluded.';
            }

            return $note;
        }

        return 'Closing balance = opening balance + credits − debits.';
    }

    private function date(string $value): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : '';
    }
}
