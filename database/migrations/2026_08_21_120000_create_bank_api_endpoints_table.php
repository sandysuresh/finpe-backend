<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('bank_api_endpoints')) {
            return;
        }

        Schema::create('bank_api_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('method', 10)->default('POST');
            $table->string('bank_path')->nullable();
            $table->text('description')->nullable();
            $table->json('request_params')->nullable();
            $table->json('response_params')->nullable();
            $table->json('sample_request')->nullable();
            $table->json('sample_response')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['bank_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_api_endpoints');
    }
};
