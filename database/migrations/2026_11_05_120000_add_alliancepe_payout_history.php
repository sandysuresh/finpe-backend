<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alliancepe_payouts', function (Blueprint $table) {
            $table->string('environment', 8)->nullable()->after('vendor_id')->index();
        });

        Schema::create('alliancepe_payout_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alliancepe_payout_id')->constrained('alliancepe_payouts')->cascadeOnDelete();
            $table->string('status', 20);
            $table->string('wallet_state', 20);
            $table->string('utr_number', 64)->nullable();
            $table->string('failure_reason', 255)->nullable();
            $table->string('source', 20);
            $table->timestamps();
            $table->index(['alliancepe_payout_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alliancepe_payout_events');
        Schema::table('alliancepe_payouts', function (Blueprint $table) {
            $table->dropColumn('environment');
        });
    }
};
