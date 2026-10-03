<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\PlainPdf;
use Illuminate\Http\Response;

class AepsApiDocController extends Controller
{
    public function __invoke(): Response
    {
        $example = json_encode([
            'client_reference' => 'TXN-EXAMPLE-1',
            'merchant' => 'MCH-EXAMPLE',
            'aadhaarNumber' => 'XXXX-XXXX-0738',
            'mobile' => '98XXXXXX10',
            'bank_iin' => '606985',
            'service' => 'BE',
            'amount' => 0,
            'device_type' => 'mantra',
            'lat' => '23.0722',
            'long' => '72.6269',
            'ip_address' => '203.0.113.10',
            'pid_data' => '[biometric data omitted]',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $lines = preg_split("/\r\n|\n/", (string) $example);
        if (! is_array($lines)) {
            $lines = [];
        }
        $lines[] = '';
        $lines[] = 'aadhaarNumber is required. The example value is masked.';
        $lines[] = 'FinPe accepts this required value as aadhaar.';

        return response(PlainPdf::apiDocumentation([
            ['client_reference', 'string', 'required', 'Unique client reference'],
            ['merchant', 'string', 'required', 'Merchant code'],
            ['aadhaarNumber', 'string', 'required', 'Customer Aadhaar. Example is masked.'],
            ['mobile', 'string', 'required', 'Customer mobile'],
            ['bank_iin', 'string', 'required', 'Bank IIN'],
            ['service', 'string', 'required', 'BE, CW, MS, CD, or AP'],
            ['amount', 'number', 'required', 'Amount. BE and MS use 0.'],
            ['device_type', 'string', 'required', 'Device name'],
            ['lat', 'string', 'required', 'Latitude'],
            ['long', 'string', 'required', 'Longitude'],
            ['ip_address', 'string', 'required', 'Device IP'],
            ['pid_data', 'string', 'required', 'Biometric payload. Not shown here.'],
        ], $lines), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="finpe-aeps-api-documentation.pdf"',
        ]);
    }
}
