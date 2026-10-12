<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wallet_topup_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('wallet_topup_requests', 'vendor_bank_account_id')) {
                $table->foreignId('vendor_bank_account_id')
                    ->nullable()
                    ->after('bank_name')
                    ->constrained('vendor_bank_accounts')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('wallet_topup_requests', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('vendor_bank_account_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wallet_topup_requests', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_topup_requests', 'vendor_bank_account_id')) {
                $table->dropConstrainedForeignId('vendor_bank_account_id');
            }
            if (Schema::hasColumn('wallet_topup_requests', 'receipt_path')) {
                $table->dropColumn('receipt_path');
            }
        });
    }
};
