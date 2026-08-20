<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('wallet_topup_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount', 18, 2);
            $table->string('payment_mode')->default('bank_transfer'); // bank_transfer, cheque, cash, neft, rtgs
            $table->string('transaction_ref')->nullable();            // UTR / cheque no
            $table->string('bank_name')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending','approved','rejected'])->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('actioned_at')->nullable();
            $table->timestamps();
            $table->index(['vendor_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('wallet_topup_requests'); }
};
