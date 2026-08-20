<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('api_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('api_key')->unique();
            $table->string('secret_key');
            $table->string('webhook_url')->nullable();
            $table->json('ip_whitelist')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('api_credentials'); }
};
