<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiCredential extends Model {
    protected $fillable = [
        'vendor_id','api_key','secret_key','webhook_url',
        'ip_whitelist','is_active','last_used_at',
    ];
    protected function casts(): array {
        return ['ip_whitelist'=>'array','is_active'=>'boolean','last_used_at'=>'datetime'];
    }
    protected $hidden = ['secret_key'];
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
}
