<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorApiAccess extends Model
{
    public const PAYOUT = 'payout';

    public const PROVIDER_PREFIX = 'payout-provider:';

    public static function providerApiCode(string $code): string
    {
        return self::PROVIDER_PREFIX.strtolower(trim($code));
    }

    public static function assignedProviderCode(int $vendorId): ?string
    {
        $code = static::query()
            ->where('vendor_id', $vendorId)
            ->where('is_enabled', true)
            ->where('api_code', 'like', self::PROVIDER_PREFIX.'%')
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->value('api_code');

        if (! is_string($code) || ! str_starts_with($code, self::PROVIDER_PREFIX)) {
            return null;
        }

        $provider = strtolower(substr($code, strlen(self::PROVIDER_PREFIX)));

        return $provider !== '' ? $provider : null;
    }

    public static function assignProvider(int $vendorId, string $code, ?int $adminId): void
    {
        $apiCode = self::providerApiCode($code);

        static::query()
            ->where('vendor_id', $vendorId)
            ->where('api_code', 'like', self::PROVIDER_PREFIX.'%')
            ->where('api_code', '!=', $apiCode)
            ->update(['is_enabled' => false]);

        static::query()->updateOrCreate(
            ['vendor_id' => $vendorId, 'api_code' => $apiCode],
            [
                'is_enabled' => true,
                'assigned_at' => now(),
                'assigned_by' => $adminId,
            ],
        );
    }

    protected $table = 'vendor_api_access';

    protected $fillable = [
        'vendor_id',
        'api_code',
        'is_enabled',
        'assigned_at',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'assigned_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_by');
    }
}
