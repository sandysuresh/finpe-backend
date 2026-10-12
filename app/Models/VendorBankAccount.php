<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorBankAccount extends Model
{
    protected $fillable = [
        'vendor_id', 'account_holder', 'account_number', 'ifsc_code', 'bank_name',
        'status', 'rejection_reason', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function topupRequests(): HasMany
    {
        return $this->hasMany(WalletTopupRequest::class);
    }

    public function maskedAccount(): string
    {
        $account = preg_replace('/\s+/', '', (string) $this->account_number) ?? '';
        $last = substr($account, -4);

        return $last === '' ? '—' : str_repeat('X', max(strlen($account) - 4, 0)).$last;
    }

    public function label(): string
    {
        return trim($this->bank_name.' · '.$this->maskedAccount());
    }
}
