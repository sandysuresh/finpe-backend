<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('admin_module_permissions')) {
            return;
        }

        Schema::create('admin_module_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->string('module');
            $table->timestamps();
            $table->unique(['admin_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_module_permissions');
    }
};
