<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'vendor_code',
        'business_name',
        'contact_name',
        'email',
        'pmt_code',
        'password',
        'phone',
        'address',
        'country',
        'kyc_status',
        'status',
        'api_enabled',
        'transaction_limit',
        'commission_type',
        'commission_value',
        'registration_step',
        'registration_completed_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'api_enabled' => 'boolean',
            'transaction_limit' => 'decimal:2',
            'commission_value' => 'decimal:2',
            'registration_step' => 'integer',
            'registration_completed_at' => 'datetime',
            'email_verified_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Wallet
    |--------------------------------------------------------------------------
    */

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Transactions
    |--------------------------------------------------------------------------
    */

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Step 2 - Business Details
    |--------------------------------------------------------------------------
    */

    public function businessDetails(): HasOne
    {
        return $this->hasOne(VendorBusinessDetail::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Step 2 - Legal Details
    |--------------------------------------------------------------------------
    */

    public function legalDetails(): HasOne
    {
        return $this->hasOne(VendorLegalDetail::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Step 3 - Promoters / Shareholders
    |--------------------------------------------------------------------------
    */

    public function promoters(): HasMany
    {
        return $this->hasMany(VendorPromoterShareholder::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Step 4 - Directors / KMP
    |--------------------------------------------------------------------------
    */

    public function directors(): HasMany
    {
        return $this->hasMany(VendorDirector::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Step 4 - Team & IT
    |--------------------------------------------------------------------------
    */

    public function teamItDetails(): HasOne
    {
        return $this->hasOne(VendorTeamItDetail::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Step 5 - 3 Years Business Plan
    |--------------------------------------------------------------------------
    */

    public function businessPlans(): HasMany
    {
        return $this->hasMany(VendorBusinessPlan::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Step 6 - Evaluation
    |--------------------------------------------------------------------------
    */

    public function evaluation(): HasOne
    {
        return $this->hasOne(VendorEvaluation::class);
    }
}