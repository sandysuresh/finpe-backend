<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_evaluations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->cascadeOnDelete();

            // CA / Corporate Agent details
            $table->string('ca_name')->nullable();
            $table->string('ca_constitution')->nullable();
            $table->date('ca_incorporation_date')->nullable();

            // Financial strength
            $table->decimal('networth', 18, 2)->nullable();
            $table->string('credit_rating')->nullable();

            // Banking relationship
            $table->string('dealing_with_bank_since')->nullable();
            $table->date('contract_expiry_date')->nullable();

            // Evaluation
            $table->text('engagement_scope')->nullable();
            $table->text('open_risk_issues')->nullable();
            $table->text('documentation_status')->nullable();

            $table->text('conflict_of_interest')->nullable();
            $table->text('terminated_or_penalties')->nullable();
            $table->text('rbi_defaulter')->nullable();

            $table->text('recommendations')->nullable();

            $table->timestamps();

            $table->unique('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_evaluations');
    }
};