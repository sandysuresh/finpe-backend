<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_legal_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->cascadeOnDelete();

            $table->string('entity_type');
            $table->string('registration_body')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('tax_identification')->nullable();

            $table->boolean('rbi_regulated')->default(false);

            $table->unsignedSmallInteger('incorporation_year')->nullable();

            $table->unsignedSmallInteger('merchant_acquiring_years')
                ->nullable();

            $table->text('additional_licenses')->nullable();

            $table->timestamps();

            $table->unique('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_legal_details');
    }
};