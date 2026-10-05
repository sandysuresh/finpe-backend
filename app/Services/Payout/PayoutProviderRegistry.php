<?php

namespace App\Services\Payout;

use App\Exceptions\PayoutException;
use Illuminate\Contracts\Container\Container;
use Throwable;

class PayoutProviderRegistry
{
    /** @var list<array{code: string, name: string, environment: string, active: bool}>|null */
    private ?array $catalog = null;

    public function __construct(private Container $container) {}

    /**
     * Registered payout providers from config('payout.providers').
     *
     * @return list<array{code: string, name: string, environment: string, active: bool}>
     */
    public function catalog(): array
    {
        if ($this->catalog !== null) {
            return $this->catalog;
        }

        $configured = strtolower((string) config('payout.provider', ''));
        $map = config('payout.providers', []);
        $rows = [];
        if (is_array($map)) {
            foreach ($map as $code => $class) {
                $code = strtolower((string) $code);
                if ($code === '' || ! is_string($class) || ! is_a($class, PayoutProvider::class, true)) {
                    continue;
                }

                try {
                    $provider = $this->container->make($class);
                } catch (Throwable) {
                    continue;
                }

                if (! $provider instanceof PayoutProvider || $provider->code() !== $code) {
                    continue;
                }

                $rows[] = [
                    'code' => $provider->code(),
                    'name' => $provider->name(),
                    'environment' => $provider->environment(),
                    'active' => $provider->code() === $configured,
                ];
            }
        }

        return $this->catalog = $rows;
    }

    public function displayName(?string $code): string
    {
        $code = strtolower(trim((string) $code));
        foreach ($this->catalog() as $row) {
            if ($row['code'] === $code) {
                return $row['name'];
            }
        }

        return $code !== '' ? $code : '—';
    }

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
