<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transactions')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'merchant_ref')) {
                $table->string('merchant_ref', 80)->nullable()->after('reference');
            }
            if (! Schema::hasColumn('transactions', 'beneficiary_bank_code')) {
                $table->string('beneficiary_bank_code', 20)->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('transactions', 'beneficiary_mobile')) {
                $table->string('beneficiary_mobile', 15)->nullable()->after('beneficiary_bank_code');
            }
            if (! Schema::hasColumn('transactions', 'payment_purpose')) {
                $table->string('payment_purpose', 20)->nullable()->after('beneficiary_mobile');
            }
            if (! Schema::hasColumn('transactions', 'beneficiary_location')) {
                $table->string('beneficiary_location', 20)->nullable()->after('payment_purpose');
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->unique(['vendor_id', 'merchant_ref']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('transactions')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['vendor_id', 'merchant_ref']);
            foreach (['beneficiary_location', 'payment_purpose', 'beneficiary_mobile', 'beneficiary_bank_code', 'merchant_ref'] as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
