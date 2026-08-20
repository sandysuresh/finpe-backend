<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('account_number', 30);
            $table->string('ifsc_code', 15)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('mobile', 15)->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index(['vendor_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('beneficiaries'); }
};
