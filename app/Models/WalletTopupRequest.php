<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTopupRequest extends Model
{
    protected $fillable = [
        'vendor_id','reference','amount','payment_mode',
        'transaction_ref','bank_name','vendor_bank_account_id','receipt_path','remarks',
        'status','admin_note','approved_by','actioned_at',
    ];

    protected function casts(): array {
        return [
            'amount'      => 'decimal:2',
            'actioned_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo    { return $this->belongsTo(Vendor::class); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(Admin::class, 'approved_by'); }
    public function vendorBankAccount(): BelongsTo { return $this->belongsTo(VendorBankAccount::class); }
}
