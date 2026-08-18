<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_business_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->cascadeOnDelete();

            $table->string('month', 20);

            $table->unsignedBigInteger('customer_registrations')
                ->default(0);

            $table->unsignedBigInteger('transactions')
                ->default(0);

            $table->decimal('total_volume', 18, 2)
                ->default(0);

            $table->timestamps();

            $table->unique(
                ['vendor_id', 'month'],
                'vendor_business_plan_vendor_month_unique'
            );

            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_business_plans');
    }
};