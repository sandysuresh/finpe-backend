<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Beneficiary extends Model {
    protected $fillable = [
        'vendor_id','name','account_number','ifsc_code',
        'bank_name','mobile','email','status',
    ];
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
}
