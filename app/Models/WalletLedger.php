<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletLedger extends Model {
    protected $table = 'wallet_ledger';
    protected $fillable = [
        'vendor_id','wallet_id','type','amount',
        'balance_before','balance_after','reference',
        'description','source',
    ];
    protected function casts(): array {
        return ['amount'=>'decimal:2','balance_before'=>'decimal:2','balance_after'=>'decimal:2'];
    }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function wallet(): BelongsTo { return $this->belongsTo(Wallet::class); }
}
