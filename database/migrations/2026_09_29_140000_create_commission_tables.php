<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('merchant_id')->nullable()->constrained('merchants')->nullOnDelete();
            $table->string('service')->nullable();
            $table->string('type')->nullable();
            $table->string('calc_type', 20);
            $table->decimal('value', 10, 2);
            $table->string('status', 20)->default('active');
            $table->dateTime('effective_from')->nullable();
            $table->dateTime('effective_to')->nullable();
            $table->integer('priority')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index('service');
            $table->index('type');
            $table->index('status');
            $table->index(['effective_from', 'effective_to']);
            $table->index('priority');
        });

        Schema::create('commission_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_rule_id')->nullable()->constrained('commission_rules')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('vendor_name_snapshot')->nullable();
            $table->foreignId('merchant_id')->nullable()->constrained('merchants')->nullOnDelete();
            $table->string('merchant_code_snapshot')->nullable();
            $table->string('service_snapshot')->nullable();
            $table->string('type_snapshot')->nullable();
            $table->string('source_type', 40);
            $table->unsignedBigInteger('source_id');
            $table->string('source_reference')->nullable();
            $table->string('source_status_snapshot', 40);
            $table->decimal('base_amount', 18, 2);
            $table->string('calc_type', 20);
            $table->decimal('rate_value', 18, 2);
            $table->decimal('commission_amount', 18, 2);
            $table->string('status', 20)->default('recorded');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['source_type', 'source_id'], 'commission_entries_source_unique');
            $table->index(['vendor_id', 'created_at']);
        });

        Schema::create('commission_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_amount', 18, 2);
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('wallet_ledger_id')->nullable();
            $table->timestamps();

            $table->index(['vendor_id', 'status']);
        });

        Schema::create('commission_settlement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_settlement_id')->constrained('commission_settlements')->cascadeOnDelete();
            $table->foreignId('commission_entry_id')->constrained('commission_entries')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['commission_settlement_id', 'commission_entry_id'], 'commission_settlement_items_pair_unique');
            $table->unique('commission_entry_id', 'commission_settlement_items_entry_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_settlement_items');
        Schema::dropIfExists('commission_settlements');
        Schema::dropIfExists('commission_entries');
        Schema::dropIfExists('commission_rules');
    }
};
