<?php

namespace App\Services\Payout;

use App\Exceptions\PayoutException;
use Illuminate\Contracts\Container\Container;

class PayoutProviderRegistry
{
    public function __construct(private Container $container) {}

    public function current(): PayoutProvider
    {
        $code = strtolower((string) config('payout.provider', ''));

        if ($this->container->resolved(PayoutProvider::class)) {
            $bound = $this->container->make(PayoutProvider::class);
            if ($bound instanceof PayoutProvider && $bound->code() === $code) {
                return $bound;
            }
        }

        $class = config('payout.providers.'.$code);
        if (! is_string($class) || ! is_a($class, PayoutProvider::class, true)) {
            throw new PayoutException('Payout provider is not configured.', 500);
        }

        $provider = $this->container->make($class);
        if (! $provider instanceof PayoutProvider || $provider->code() !== $code) {
            throw new PayoutException('Payout provider is not configured.', 500);
        }

        return $provider;
    }
}
