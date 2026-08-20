<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Wallet extends Model {
    protected $fillable = ['vendor_id','balance','hold_balance'];

    protected function casts(): array {
        return ['balance'=>'decimal:2','hold_balance'=>'decimal:2'];
    }

    public function vendor(): BelongsTo  { return $this->belongsTo(Vendor::class); }
    public function ledger(): HasMany    { return $this->hasMany(WalletLedger::class); }
}
