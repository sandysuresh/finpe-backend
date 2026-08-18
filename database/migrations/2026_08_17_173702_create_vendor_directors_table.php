<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_directors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('designation');
            $table->string('pan_card_no')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('official_address')->nullable();
            $table->text('profile_past_experience')->nullable();

            $table->timestamps();

            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_directors');
    }
};