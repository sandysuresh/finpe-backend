<?php

namespace App\Support;

class BankApiCatalog
{
    public static function templates(): array
    {
        return [
            [
                'name' => 'Health Check',
                'slug' => 'health',
                'method' => 'GET',
                'bank_path' => '/health',
                'description' => 'Check if the bank API is reachable.',
                'request_params' => [],
                'response_params' => [
                    ['name' => 'success', 'type' => 'boolean', 'required' => true, 'description' => 'API available'],
                    ['name' => 'message', 'type' => 'string', 'required' => false, 'description' => 'Status text'],
                ],
                'sample_request' => [],
                'sample_response' => ['success' => true, 'message' => 'OK'],
            ],
            [
                'name' => 'Account Validate',
                'slug' => 'account-validate',
                'method' => 'POST',
                'bank_path' => '/account/validate',
                'description' => 'Validate beneficiary account before payout.',
                'request_params' => [
                    ['name' => 'account_number', 'type' => 'string', 'required' => true, 'description' => 'Bank account number'],
                    ['name' => 'ifsc_code', 'type' => 'string', 'required' => true, 'description' => 'IFSC code'],
                    ['name' => 'beneficiary_name', 'type' => 'string', 'required' => false, 'description' => 'Account holder name'],
                ],
                'response_params' => [
                    ['name' => 'success', 'type' => 'boolean', 'required' => true, 'description' => 'Validation result'],
                    ['name' => 'data.valid', 'type' => 'boolean', 'required' => true, 'description' => 'Account is valid'],
                    ['name' => 'data.account_name', 'type' => 'string', 'required' => false, 'description' => 'Name from bank'],
                ],
                'sample_request' => ['account_number' => '12345678901', 'ifsc_code' => 'SBIN0001234', 'beneficiary_name' => 'Ram Bahadur'],
                'sample_response' => ['success' => true, 'data' => ['valid' => true, 'account_name' => 'Ram Bahadur']],
            ],
            [
                'name' => 'Balance',
                'slug' => 'balance',
                'method' => 'GET',
                'bank_path' => '/balance',
                'description' => 'Fetch vendor wallet balance on FinPay.',
                'request_params' => [],
                'response_params' => [
                    ['name' => 'success', 'type' => 'boolean', 'required' => true, 'description' => 'Request status'],
                    ['name' => 'data.available_balance', 'type' => 'number', 'required' => true, 'description' => 'Available amount'],
                    ['name' => 'data.hold_balance', 'type' => 'number', 'required' => true, 'description' => 'Hold amount'],
                    ['name' => 'data.currency', 'type' => 'string', 'required' => true, 'description' => 'INR'],
                ],
                'sample_request' => [],
                'sample_response' => ['success' => true, 'data' => ['available_balance' => 150000.00, 'hold_balance' => 0, 'currency' => 'INR']],
            ],
            [
                'name' => 'Create Payout',
                'slug' => 'payout',
                'method' => 'POST',
                'bank_path' => '/payout',
                'description' => 'Send money via IMPS / NEFT / RTGS.',
                'request_params' => [
                    ['name' => 'beneficiary_name', 'type' => 'string', 'required' => true, 'description' => 'Receiver name'],
                    ['name' => 'account_number', 'type' => 'string', 'required' => true, 'description' => 'Receiver account'],
                    ['name' => 'ifsc_code', 'type' => 'string', 'required' => true, 'description' => 'IFSC'],
                    ['name' => 'amount', 'type' => 'number', 'required' => true, 'description' => 'Amount in INR'],
                    ['name' => 'service', 'type' => 'string', 'required' => true, 'description' => 'imps | neft | rtgs'],
                    ['name' => 'remarks', 'type' => 'string', 'required' => false, 'description' => 'Narration'],
                ],
                'response_params' => [
                    ['name' => 'success', 'type' => 'boolean', 'required' => true, 'description' => 'Accepted by FinPay'],
                    ['name' => 'data.reference', 'type' => 'string', 'required' => true, 'description' => 'FinPay reference'],
                    ['name' => 'data.status', 'type' => 'string', 'required' => true, 'description' => 'success | pending | failed'],
                    ['name' => 'data.bank_reference', 'type' => 'string', 'required' => false, 'description' => 'Bank UTR / control no'],
                ],
                'sample_request' => [
                    'beneficiary_name' => 'Ram Bahadur',
                    'account_number' => '12345678901',
                    'ifsc_code' => 'SBIN0001234',
                    'amount' => 2500.00,
                    'service' => 'imps',
                    'remarks' => 'Family support',
                ],
                'sample_response' => [
                    'success' => true,
                    'message' => 'Payout initiated.',
                    'data' => ['reference' => 'TXN-AB12CD34EF', 'status' => 'success', 'bank_reference' => 'BNK-998877'],
                ],
            ],
            [
                'name' => 'Payout Status',
                'slug' => 'payout-status',
                'method' => 'POST',
                'bank_path' => '/payout/status',
                'description' => 'Check status of a payout using FinPay reference.',
                'request_params' => [
                    ['name' => 'reference', 'type' => 'string', 'required' => true, 'description' => 'FinPay transaction reference'],
                ],
                'response_params' => [
                    ['name' => 'success', 'type' => 'boolean', 'required' => true, 'description' => 'Request status'],
                    ['name' => 'data.status', 'type' => 'string', 'required' => true, 'description' => 'success | pending | failed'],
                    ['name' => 'data.bank_reference', 'type' => 'string', 'required' => false, 'description' => 'Bank reference'],
                ],
                'sample_request' => ['reference' => 'TXN-AB12CD34EF'],
                'sample_response' => ['success' => true, 'data' => ['reference' => 'TXN-AB12CD34EF', 'status' => 'success', 'bank_reference' => 'BNK-998877']],
            ],
            [
                'name' => 'Payout List',
                'slug' => 'payout-list',
                'method' => 'GET',
                'bank_path' => '/payouts',
                'description' => 'Recent payouts for this vendor.',
                'request_params' => [],
                'response_params' => [
                    ['name' => 'success', 'type' => 'boolean', 'required' => true, 'description' => 'Request status'],
                    ['name' => 'data', 'type' => 'array', 'required' => true, 'description' => 'List of payouts'],
                ],
                'sample_request' => [],
                'sample_response' => ['success' => true, 'data' => [['reference' => 'TXN-AB12CD34EF', 'amount' => 2500, 'status' => 'success']]],
            ],
            [
                'name' => 'Cancel Payout',
                'slug' => 'payout-cancel',
                'method' => 'POST',
                'bank_path' => '/payout/cancel',
                'description' => 'Cancel a pending payout if the bank allows it.',
                'request_params' => [
                    ['name' => 'reference', 'type' => 'string', 'required' => true, 'description' => 'FinPay reference'],
                    ['name' => 'reason', 'type' => 'string', 'required' => false, 'description' => 'Cancel reason'],
                ],
                'response_params' => [
                    ['name' => 'success', 'type' => 'boolean', 'required' => true, 'description' => 'Cancel accepted'],
                    ['name' => 'data.status', 'type' => 'string', 'required' => true, 'description' => 'cancelled | pending'],
                ],
                'sample_request' => ['reference' => 'TXN-AB12CD34EF', 'reason' => 'Customer request'],
                'sample_response' => ['success' => true, 'data' => ['reference' => 'TXN-AB12CD34EF', 'status' => 'cancelled']],
            ],
            [
                'name' => 'Service Charges',
                'slug' => 'charges',
                'method' => 'POST',
                'bank_path' => '/charges',
                'description' => 'Get service charge for an amount and service.',
                'request_params' => [
                    ['name' => 'amount', 'type' => 'number', 'required' => true, 'description' => 'Payout amount'],
                    ['name' => 'service', 'type' => 'string', 'required' => true, 'description' => 'imps | neft | rtgs'],
                ],
                'response_params' => [
                    ['name' => 'success', 'type' => 'boolean', 'required' => true, 'description' => 'Request status'],
                    ['name' => 'data.charge', 'type' => 'number', 'required' => true, 'description' => 'Fee amount'],
                    ['name' => 'data.total', 'type' => 'number', 'required' => true, 'description' => 'Amount + charge'],
                ],
                'sample_request' => ['amount' => 2500, 'service' => 'imps'],
                'sample_response' => ['success' => true, 'data' => ['charge' => 5.00, 'total' => 2505.00]],
            ],
            [
                'name' => 'Add Beneficiary',
                'slug' => 'beneficiary-add',
                'method' => 'POST',
                'bank_path' => '/beneficiaries',
                'description' => 'Save a beneficiary for future payouts.',
                'request_params' => [
                    ['name' => 'name', 'type' => 'string', 'required' => true, 'description' => 'Beneficiary name'],
                    ['name' => 'account_number', 'type' => 'string', 'required' => true, 'description' => 'Account number'],
                    ['name' => 'ifsc_code', 'type' => 'string', 'required' => true, 'description' => 'IFSC'],
                    ['name' => 'bank_name', 'type' => 'string', 'required' => false, 'description' => 'Bank name'],
                ],
                'response_params' => [
                    ['name' => 'success', 'type' => 'boolean', 'required' => true, 'description' => 'Saved'],
                    ['name' => 'data.id', 'type' => 'string', 'required' => true, 'description' => 'Beneficiary id'],
                ],
                'sample_request' => ['name' => 'Ram Bahadur', 'account_number' => '12345678901', 'ifsc_code' => 'SBIN0001234', 'bank_name' => 'SBI'],
                'sample_response' => ['success' => true, 'data' => ['id' => 'BEN-1001']],
            ],
            [
                'name' => 'Beneficiary List',
                'slug' => 'beneficiary-list',
                'method' => 'GET',
                'bank_path' => '/beneficiaries',
                'description' => 'List saved beneficiaries.',
                'request_params' => [],
                'response_params' => [
                    ['name' => 'success', 'type' => 'boolean', 'required' => true, 'description' => 'Request status'],
                    ['name' => 'data', 'type' => 'array', 'required' => true, 'description' => 'Beneficiaries'],
                ],
                'sample_request' => [],
                'sample_response' => ['success' => true, 'data' => [['id' => 'BEN-1001', 'name' => 'Ram Bahadur', 'account_number' => '12345678901']]],
            ],
            [
                'name' => 'Refund',
                'slug' => 'refund',
                'method' => 'POST',
                'bank_path' => '/refund',
                'description' => 'Request refund for a failed or reversed payout.',
                'request_params' => [
                    ['name' => 'reference', 'type' => 'string', 'required' => true, 'description' => 'Original FinPay reference'],
                    ['name' => 'reason', 'type' => 'string', 'required' => false, 'description' => 'Refund reason'],
                ],
                'response_params' => [
                    ['name' => 'success', 'type' => 'boolean', 'required' => true, 'description' => 'Refund accepted'],
                    ['name' => 'data.refund_reference', 'type' => 'string', 'required' => true, 'description' => 'Refund reference'],
                ],
                'sample_request' => ['reference' => 'TXN-AB12CD34EF', 'reason' => 'Failed at bank'],
                'sample_response' => ['success' => true, 'data' => ['refund_reference' => 'RFD-112233', 'status' => 'pending']],
            ],
            [
                'name' => 'Statement',
                'slug' => 'statement',
                'method' => 'POST',
                'bank_path' => '/statement',
                'description' => 'Download / fetch transaction statement for a date range.',
                'request_params' => [
                    ['name' => 'date_from', 'type' => 'string', 'required' => true, 'description' => 'YYYY-MM-DD'],
                    ['name' => 'date_to', 'type' => 'string', 'required' => true, 'description' => 'YYYY-MM-DD'],
                ],
                'response_params' => [
                    ['name' => 'success', 'type' => 'boolean', 'required' => true, 'description' => 'Request status'],
                    ['name' => 'data.count', 'type' => 'number', 'required' => true, 'description' => 'Rows returned'],
                    ['name' => 'data.rows', 'type' => 'array', 'required' => true, 'description' => 'Statement rows'],
                ],
                'sample_request' => ['date_from' => '2026-08-01', 'date_to' => '2026-08-21'],
                'sample_response' => ['success' => true, 'data' => ['count' => 1, 'rows' => [['reference' => 'TXN-AB12CD34EF', 'amount' => 2500, 'status' => 'success']]]],
            ],
        ];
    }
}
