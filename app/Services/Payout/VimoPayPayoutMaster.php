<?php

namespace App\Services\Payout;

use App\Exceptions\AepsException;
use App\Exceptions\PayoutException;
use App\Services\Aeps\VimoPayClient;

class VimoPayPayoutMaster implements PayoutMasterProvider
{
    public function __construct(private VimoPayClient $client) {}

    public function banks(): array
    {
        return $this->list('payout_banks');
    }

    public function purposes(): array
    {
        return $this->list('payout_purposes');
    }

    public function states(): array
    {
        return $this->list('payout_states');
    }

    /**
     * @return array{success: bool, message: string, data: list<array{description: string, code: string}>}
     */
    private function list(string $operation): array
    {
        try {
            $envelope = $this->client->get($this->client->path($operation));
        } catch (AepsException) {
            throw new PayoutException('Payout provider request failed.', 502);
        }

        if (($envelope['successStatus'] ?? null) === false || (string) ($envelope['responseCode'] ?? '') !== '000') {
            throw new PayoutException($this->safeMessage($envelope['message'] ?? null), 502);
        }

        $rows = $envelope['data'] ?? null;
        if (! is_array($rows) || array_is_list($rows) === false) {
            throw new PayoutException('Payout provider returned an unreadable response.', 502);
        }

        $data = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                throw new PayoutException('Payout provider returned an unreadable response.', 502);
            }
            $data[] = [
                'description' => is_scalar($row['description'] ?? null) ? (string) $row['description'] : '',
                'code' => is_scalar($row['code'] ?? null) ? (string) $row['code'] : '',
            ];
        }

        return [
            'success' => true,
            'message' => $this->safeMessage($envelope['message'] ?? null),
            'data' => $data,
        ];
    }

    private function safeMessage(mixed $message): string
    {
        if (! is_string($message) || $message === '') {
            return 'Payout provider request failed.';
        }
        if (preg_match('/https?:\/\/|vimopay/i', $message) === 1) {
            return 'Payout provider request failed.';
        }

        return $message;
    }
}
