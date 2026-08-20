<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('wallet_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 18, 2);
            $table->decimal('balance_before', 18, 2);
            $table->decimal('balance_after', 18, 2);
            $table->string('reference')->nullable();
            $table->string('description')->nullable();
            $table->string('source')->default('manual'); // manual, transaction, settlement, refund
            $table->nullableMorphs('sourceable');
            $table->timestamps();
            $table->index(['vendor_id', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('wallet_ledger'); }
};
