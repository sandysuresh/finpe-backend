<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vendor_api_access')) {
            return;
        }

        Schema::create('vendor_api_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('api_code', 40);
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            $table->unique(['vendor_id', 'api_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_api_access');
    }
};
