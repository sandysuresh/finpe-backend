<?php

use Illuminate\Database\Migrations\Migration;

// Registration fields (password, pmt_code, registration_step, etc.)
// are now included in the original create_vendors_table migration.
// This migration is kept as a no-op to avoid breaking existing deployments.
return new class extends Migration
{
    public function up(): void {}
    public function down(): void {}
};
