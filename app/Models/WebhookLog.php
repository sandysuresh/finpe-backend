<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model {
    protected $fillable = [
        'vendor_id','event','url','payload','response_code',
        'response_body','status','attempts','next_retry_at',
    ];
    protected function casts(): array {
        return ['payload'=>'array','next_retry_at'=>'datetime'];
    }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
}
