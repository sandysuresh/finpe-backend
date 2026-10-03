<?php

namespace App\Services\Payout;

interface PayoutMasterProvider
{
    /**
     * @return array{success: bool, message: string, data: list<array{description: string, code: string}>}
     */
    public function banks(): array;

    /**
     * @return array{success: bool, message: string, data: list<array{description: string, code: string}>}
     */
    public function purposes(): array;

    /**
     * @return array{success: bool, message: string, data: list<array{description: string, code: string}>}
     */
    public function states(): array;
}
