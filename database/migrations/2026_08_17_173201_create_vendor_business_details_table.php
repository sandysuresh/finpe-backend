<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_business_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->cascadeOnDelete();

            $table->string('entity_type')->nullable();
            $table->string('registered_with')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('pan_tin')->nullable();

            $table->boolean('rbi_regulated')->nullable();

            $table->unsignedSmallInteger('incorporation_year')
                ->nullable();

            $table->unsignedSmallInteger('merchant_acquiring_years')
                ->nullable();

            $table->text('corporate_office_address')->nullable();
            $table->text('additional_licenses')->nullable();

            $table->timestamps();

            $table->unique('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_business_details');
    }
};