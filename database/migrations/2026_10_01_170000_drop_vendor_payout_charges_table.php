<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('vendor_payout_charges');
    }

    public function down(): void
    {
        // The separate vendor payout charge table is not restored.
    }
};
