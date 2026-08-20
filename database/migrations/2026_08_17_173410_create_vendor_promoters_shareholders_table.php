<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_promoter_shareholders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->cascadeOnDelete();

            $table->string('full_name');
            $table->decimal('shareholding_percentage', 5, 2);
            $table->string('pan_card_no')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('official_address')->nullable();

            $table->timestamps();

            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_promoters_shareholders');
    }
};