<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model {
    protected $fillable = [
        'vendor_id','method','endpoint','status_code',
        'request_payload','response_payload','ip_address','response_time_ms',
    ];
    protected function casts(): array {
        return ['request_payload'=>'array','response_payload'=>'array'];
    }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
}
