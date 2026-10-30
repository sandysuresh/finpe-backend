<?php

namespace App\Services\Payout;

use App\Exceptions\AepsException;
use App\Exceptions\PayoutException;
use App\Services\Aeps\VimoPayClient;
use Illuminate\Support\Facades\Log;

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

        if ($operation === 'payout_states') {
            $this->logStateCatalog($data);
        }

        return [
            'success' => true,
            'message' => $this->safeMessage($envelope['message'] ?? null),
            'data' => $data,
        ];
    }

    /**
     * @param  list<array{description: string, code: string}>  $data
     */
    private function logStateCatalog(array $data): void
    {
        Log::info('payout_state_catalog', [
            'row_count' => count($data),
            'states' => array_map(function (array $row): array {
                $code = (string) ($row['code'] ?? '');
                $description = (string) ($row['description'] ?? '');
                if (preg_match('/bearer\s+[A-Za-z0-9._\-]+|sk_|pk_|secret|salt|encrypt|https?:\/\//i', $description) === 1) {
                    $description = '';
                }

                return [
                    'trimmed_code' => trim($code),
                    'description' => mb_substr(trim($description), 0, 120),
                    'raw_length' => strlen($code),
                    'leading_whitespace' => preg_match('/^\s/u', $code) === 1,
                    'trailing_whitespace' => preg_match('/\s$/u', $code) === 1,
                ];
            }, $data),
        ]);
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
