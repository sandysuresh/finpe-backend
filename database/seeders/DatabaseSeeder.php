<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@finpay.test',
            'password' => 'password',
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $vendors = collect([
            ['NepalPay Pvt. Ltd.', 'nepalpay@example.com'],
            ['Himal Remit', 'himal@example.com'],
            ['KTM Transfer', 'ktm@example.com'],
            ['Everest Pay', 'everest@example.com'],
            ['Global Remit', 'global@example.com'],
        ])->map(function ($item) {
            $vendor = Vendor::create([
                'name' => $item[0],
                'email' => $item[1],
                'mobile' => '98'.random_int(10000000, 99999999),
                'status' => 'active',
            ]);

            Wallet::create([
                'vendor_id' => $vendor->id,
                'balance' => random_int(500000, 1800000) / 100,
                'hold_balance' => random_int(0, 50000) / 100,
            ]);

            return $vendor;
        });

        $names = ['Ram Bahadur', 'Sita Gurung', 'Hari Shrestha', 'Bibek Rana', 'Deepak Thapa', 'Maya Tamang', 'Ramesh Karki'];
        $statuses = ['success', 'success', 'success', 'success', 'pending', 'failed'];

        for ($i = 1; $i <= 100; $i++) {
            Transaction::create([
                'vendor_id' => $vendors->random()->id,
                'reference' => 'TXN'.now()->format('Ymd').str_pad((string)$i, 6, '0', STR_PAD_LEFT),
                'amount' => random_int(500, 100000),
                'type' => 'payout',
                'service' => 'nepal_ime',
                'beneficiary_name' => $names[array_rand($names)],
                'status' => $statuses[array_rand($statuses)],
                'created_at' => now()->subDays(random_int(0, 30))->subMinutes(random_int(0, 1200)),
                'updated_at' => now(),
            ]);
        }
    }
}