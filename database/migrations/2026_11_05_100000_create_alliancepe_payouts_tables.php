<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alliancepe_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('merchant_order_id', 64)->unique();
            $table->string('provider_payout_id', 80)->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('pending')->index();
            $table->string('wallet_state', 20)->default('debited');
            $table->string('beneficiary_name', 100);
            $table->string('bank_name', 120);
            $table->string('account_number', 18);
            $table->string('ifsc_code', 11);
            $table->string('phone', 15);
            $table->string('email', 190);
            $table->string('transfer_mode', 8);
            $table->string('remarks', 255)->nullable();
            $table->string('utr_number', 64)->nullable();
            $table->string('failure_reason', 255)->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('provider_code', 64)->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('alliancepe_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('webhook_id', 80)->unique();
            $table->string('event', 40);
            $table->string('provider_payout_id', 80)->nullable();
            $table->string('merchant_order_id', 64)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alliancepe_webhook_events');
        Schema::dropIfExists('alliancepe_payouts');
    }
};
