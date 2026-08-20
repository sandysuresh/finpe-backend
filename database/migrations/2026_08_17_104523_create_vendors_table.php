<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
         if (Schema::hasTable('vendors')) {
        return;
    }
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();

            $table->string('vendor_code', 30)->unique();
            $table->string('pmt_code', 20)->unique()->nullable();

            $table->string('business_name');
            $table->string('contact_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone', 20);

            $table->text('address')->nullable();
            $table->string('country', 100)->default('Nepal');

            $table->enum('kyc_status', [
                'pending',
                'submitted',
                'verified',
                'rejected',
            ])->default('pending');

            $table->enum('status', [
                'active',
                'inactive',
                'suspended',
            ])->default('active');

            $table->boolean('api_enabled')->default(false);

            $table->decimal('transaction_limit', 15, 2)->default(0);

            $table->enum('commission_type', [
                'percentage',
                'fixed',
            ])->default('percentage');

            $table->decimal('commission_value', 10, 2)->default(0);

            $table->unsignedTinyInteger('registration_step')->default(1);
            $table->timestamp('registration_completed_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();

            $table->timestamps();

            $table->index('status');
            $table->index('kyc_status');
            $table->index('api_enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
