<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transactions') || Schema::hasColumn('transactions', 'rrn')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('rrn', 40)->nullable()->after('bank_reference');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('transactions') || ! Schema::hasColumn('transactions', 'rrn')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('rrn');
        });
    }
};
