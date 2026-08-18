<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_team_it_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->cascadeOnDelete();

            // Team / Employee details
            $table->unsignedInteger('total_employees')->default(0);
            $table->unsignedInteger('technology_employees')->default(0);
            $table->unsignedInteger('sales_employees')->default(0);
            $table->unsignedInteger('support_employees')->default(0);
            $table->unsignedInteger('admin_finance_hr_employees')->default(0);

            // IT System Overview
            $table->text('it_system_overview')->nullable();

            // System / Infrastructure
            $table->text('processing_systems')->nullable();
            $table->text('applications')->nullable();
            $table->text('database_system')->nullable();
            $table->text('switch_system')->nullable();
            $table->text('terminals')->nullable();

            // Risk / Management systems
            $table->text('fraud_risk_management')->nullable();
            $table->text('merchant_agent_management')->nullable();
            $table->text('merchant_agent_portal')->nullable();
            $table->text('additional_systems')->nullable();

            $table->timestamps();

            $table->unique('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_team_it_details');
    }
};