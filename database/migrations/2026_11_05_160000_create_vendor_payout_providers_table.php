<?php

use App\Support\AssignedPayoutProviders;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vendor_payout_providers')) {
            Schema::create('vendor_payout_providers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->string('provider_code', 32);
                $table->timestamp('assigned_at')->nullable();
                $table->foreignId('assigned_by')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamps();
                $table->unique(['vendor_id', 'provider_code']);
            });
        }

        AssignedPayoutProviders::backfill();
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_payout_providers');
    }
};
