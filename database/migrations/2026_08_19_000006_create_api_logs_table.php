<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('method', 10);
            $table->string('endpoint');
            $table->integer('status_code');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->timestamps();
            $table->index(['vendor_id', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('api_logs'); }
};
