<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40)->unique();
            $table->string('client_reference', 80);
            $table->string('provider_merchant_id')->nullable();
            $table->string('provider_ref')->nullable();
            $table->string('pipe', 20)->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone_masked', 32)->nullable();
            $table->string('aadhaar_masked', 32)->nullable();
            $table->string('provider_status_code', 8)->nullable();
            $table->string('onboarding_status', 40)->default('initiated');
            $table->string('provider_status_description')->nullable();
            $table->timestamp('two_fa_at')->nullable();
            $table->json('sanitized_response')->nullable();
            $table->timestamps();
            $table->unique(['vendor_id', 'client_reference']);
        });

        Schema::create('aeps_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 40)->unique();
            $table->string('client_reference', 80);
            $table->char('request_hash', 64);
            $table->string('service', 10);
            $table->decimal('amount', 12, 2);
            $table->string('status', 32)->default('initiated')->index();
            $table->string('provider_status_code', 8)->nullable();
            $table->string('provider_txn_ref')->nullable();
            $table->string('rrn')->nullable();
            $table->string('npci_code', 16)->nullable();
            $table->string('npci_message')->nullable();
            $table->string('provider_merchant_status')->nullable();
            $table->string('provider_status_description')->nullable();
            $table->string('provider_available_balance')->nullable();
            $table->text('provider_transaction_list')->nullable();
            $table->string('aadhaar_masked', 32)->nullable();
            $table->string('bank_iin', 16)->nullable();
            $table->string('wallet_effect', 40)->default('BUSINESS_RULE_PENDING');
            $table->string('charge_effect', 40)->default('BUSINESS_RULE_PENDING');
            $table->string('failure_reason')->nullable();
            $table->json('sanitized_response')->nullable();
            $table->timestamps();
            $table->unique(['vendor_id', 'client_reference']);
            $table->index(['vendor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aeps_transactions');
        Schema::dropIfExists('merchants');
    }
};
