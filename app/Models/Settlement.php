<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settlement extends Model {
    protected $fillable = [
        'vendor_id','reference','amount','fee','net_amount',
        'status','bank_name','account_number','ifsc_code',
        'remarks','settled_at',
    ];
    protected function casts(): array {
        return ['amount'=>'decimal:2','fee'=>'decimal:2','net_amount'=>'decimal:2','settled_at'=>'datetime'];
    }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
}
