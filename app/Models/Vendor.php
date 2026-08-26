<?php
namespace App\Models;

use App\Models\Concerns\HasEncryptedRouteKey;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\{HasOne, HasMany, BelongsToMany};

class Vendor extends Authenticatable
{
    use Notifiable, HasEncryptedRouteKey;

    protected $fillable = [
        'vendor_code','pmt_code','business_name','contact_name',
        'email','password','phone','address','country',
        'kyc_status','kyc_comment','kyc_reviewed_at','kyc_reviewed_by',
        'status','api_enabled','transaction_limit',
        'commission_type','commission_value','registration_step',
        'registration_completed_at','email_verified_at',
    ];

    protected $hidden = ['password','remember_token'];

    protected function casts(): array {
        return [
            'password'                  => 'hashed',
            'api_enabled'               => 'boolean',
            'transaction_limit'         => 'decimal:2',
            'commission_value'          => 'decimal:2',
            'registration_step'         => 'integer',
            'registration_completed_at' => 'datetime',
            'email_verified_at'         => 'datetime',
            'kyc_reviewed_at'           => 'datetime',
        ];
    }

    public function kycReviewer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Admin::class, 'kyc_reviewed_by');
    }

    public function wallet(): HasOne               { return $this->hasOne(Wallet::class); }
    public function transactions(): HasMany        { return $this->hasMany(Transaction::class); }
    public function beneficiaries(): HasMany       { return $this->hasMany(Beneficiary::class); }
    public function settlements(): HasMany         { return $this->hasMany(Settlement::class); }
    public function apiCredential(): HasOne        { return $this->hasOne(ApiCredential::class); }
    public function webhookLogs(): HasMany         { return $this->hasMany(WebhookLog::class); }
    public function apiLogs(): HasMany             { return $this->hasMany(ApiLog::class); }
    public function topupRequests(): HasMany       { return $this->hasMany(WalletTopupRequest::class); }
    public function banks(): BelongsToMany
    {
        return $this->belongsToMany(Bank::class, 'vendor_banks')
            ->withPivot('is_enabled')
            ->withTimestamps();
    }

    public function assignedBanks(): BelongsToMany
    {
        return $this->banks()
            ->wherePivot('is_enabled', true)
            ->where('banks.is_active', true);
    }

    // Registration wizard relations
    public function businessDetails(): HasOne      { return $this->hasOne(VendorBusinessDetail::class); }
    public function legalDetails(): HasOne         { return $this->hasOne(VendorLegalDetail::class); }
    public function promoters(): HasMany           { return $this->hasMany(VendorPromoterShareholder::class); }
    public function directors(): HasMany           { return $this->hasMany(VendorDirector::class); }
    public function teamItDetails(): HasOne        { return $this->hasOne(VendorTeamItDetail::class); }
    public function businessPlans(): HasMany       { return $this->hasMany(VendorBusinessPlan::class); }
    public function evaluation(): HasOne           { return $this->hasOne(VendorEvaluation::class); }
    public function kycReviews(): HasMany          { return $this->hasMany(VendorKycReview::class)->latest(); }
}
