<?php

namespace Tests\Support;

use App\Exceptions\PayoutException;
use App\Services\Payout\PayoutMasterProvider;

class FakePayoutMaster implements PayoutMasterProvider
{
    public bool $fail = false;

    /** @var list<string> */
    public array $calls = [];

    public function banks(): array
    {
        return $this->hit('banks', [
            ['description' => 'Axis Bank', 'code' => '001'],
        ]);
    }

    public function purposes(): array
    {
        return $this->hit('purposes', [
            ['description' => 'Payout', 'code' => '004'],
        ]);
    }

    public function states(): array
    {
        return $this->hit('states', [
            ['description' => 'Assam', 'code' => 'AS '],
        ]);
    }

    /**
     * @param  list<array{description: string, code: string}>  $data
     * @return array{success: bool, message: string, data: list<array{description: string, code: string}>}
     */
    private function hit(string $name, array $data): array
    {
        $this->calls[] = $name;
        if ($this->fail) {
            throw new PayoutException('Payout provider request failed.', 502);
        }

        return [
            'success' => true,
            'message' => 'Success',
            'data' => $data,
        ];
    }
}
