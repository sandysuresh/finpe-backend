<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class AlliancePePayout extends Model
{
    protected $table = 'alliancepe_payouts';

    protected $fillable = [
        'vendor_id', 'environment', 'merchant_order_id', 'provider_payout_id', 'amount',
        'commission_rule_id', 'commission_type', 'commission_rate', 'commission_amount', 'total_debited',
        'status', 'wallet_state',
        'beneficiary_name', 'bank_name', 'account_number', 'ifsc_code', 'phone', 'email',
        'transfer_mode', 'remarks', 'utr_number', 'failure_reason', 'http_status', 'provider_code',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'total_debited' => 'decimal:2',
            'refunded_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(AlliancePePayoutEvent::class, 'alliancepe_payout_id')->orderBy('id');
    }

    public function maskedAccount(): string
    {
        return self::maskDigits((string) $this->account_number);
    }

    public function maskedPhone(): string
    {
        return self::maskDigits((string) $this->phone);
    }

    public function maskedEmail(): string
    {
        $email = (string) $this->email;
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2 || $parts[0] === '') {
            return '***';
        }

        return substr($parts[0], 0, 1).'***@'.$parts[1];
    }

    public function processedAt(): ?Carbon
    {
        $event = $this->events->first(fn (AlliancePePayoutEvent $event) => in_array($event->status, ['processing', 'completed'], true));

        return $event?->created_at;
    }

    /**
     * @param  Builder<AlliancePePayout>  $query
     * @param  array{vendor_id?: int|string|null, status?: string|null, transfer_mode?: string|null, environment?: string|null, transaction_id?: string|null, merchant_order_id?: string|null, payout_id?: string|null, date_from?: string|null, date_to?: string|null, search?: string|null}  $filters
     * @return Builder<AlliancePePayout>
     */
    public function scopeHistory(Builder $query, array $filters): Builder
    {
        $vendorId = $filters['vendor_id'] ?? null;
        $status = self::filterText($filters['status'] ?? null);
        $mode = self::filterText($filters['transfer_mode'] ?? null);
        $environment = self::filterText($filters['environment'] ?? null);
        $transactionId = self::filterText($filters['transaction_id'] ?? null);
        $merchantOrderId = self::filterText($filters['merchant_order_id'] ?? null);
        $payoutId = self::filterText($filters['payout_id'] ?? null);
        $search = self::filterText($filters['search'] ?? null);

        return $query
            ->when($vendorId !== null && $vendorId !== '', fn (Builder $query) => $query->where('vendor_id', (int) $vendorId))
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->when($mode, fn (Builder $query) => $query->where('transfer_mode', strtolower($mode)))
            ->when($environment, fn (Builder $query) => $query->where('environment', strtolower($environment)))
            ->when($transactionId !== null && ctype_digit($transactionId), fn (Builder $query) => $query->whereKey((int) $transactionId))
            ->when($merchantOrderId, fn (Builder $query) => $query->where('merchant_order_id', $merchantOrderId))
            ->when($payoutId, fn (Builder $query) => $query->where('provider_payout_id', $payoutId))
            ->when(self::filterText($filters['date_from'] ?? null), fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when(self::filterText($filters['date_to'] ?? null), fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when($search, function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('merchant_order_id', $search)->orWhere('provider_payout_id', $search);
                    if (ctype_digit($search)) {
                        $query->orWhere('id', (int) $search);
                    }
                });
            });
    }

    public static function maskDigits(string $value): string
    {
        if (strlen($value) <= 4) {
            return str_repeat('*', strlen($value));
        }

        return str_repeat('*', strlen($value) - 4).substr($value, -4);
    }

    private static function filterText(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $text = trim($value);

        return $text === '' ? null : $text;
    }
}
