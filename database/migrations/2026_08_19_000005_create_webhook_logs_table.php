<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->string('url');
            $table->json('payload');
            $table->integer('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();
            $table->index(['vendor_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('webhook_logs'); }
};
