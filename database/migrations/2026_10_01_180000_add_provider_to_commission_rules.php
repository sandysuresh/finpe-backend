<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('commission_rules')) {
            return;
        }

        if (! Schema::hasColumn('commission_rules', 'provider')) {
            Schema::table('commission_rules', function (Blueprint $table) {
                $table->string('provider', 40)->nullable()->after('vendor_id');
                $table->index('provider');
            });
        }

        DB::table('commission_rules')
            ->whereNull('provider')
            ->where(function ($query) {
                $query->whereNull('type')->orWhere('type', 'payout');
            })
            ->update([
                'provider' => 'vimopay',
                'type' => 'payout',
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('commission_rules') || ! Schema::hasColumn('commission_rules', 'provider')) {
            return;
        }

        Schema::table('commission_rules', function (Blueprint $table) {
            $table->dropIndex(['provider']);
            $table->dropColumn('provider');
        });
    }
};
