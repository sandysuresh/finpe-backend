<?php

namespace App\Services\Aeps;

/**
 * Finpe Vendor wallet accounting for AePS is not defined by the VimoPay specification.
 * No debit, credit, charge, refund, reversal, or settlement is applied until a business rule exists.
 */
final class AepsWalletRule
{
    public const PENDING = 'BUSINESS_RULE_PENDING';
}
