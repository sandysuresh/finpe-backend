<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transactions') || Schema::hasColumn('transactions', 'payout_provider')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payout_provider', 40)->nullable()->after('payout_charge');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('transactions') || ! Schema::hasColumn('transactions', 'payout_provider')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('payout_provider');
        });
    }
};
