<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('api_credentials')) {
            return;
        }

        $column = collect(DB::select("SHOW COLUMNS FROM api_credentials WHERE Field = 'secret_key'"))->first();
        if (! $column || str_contains(strtolower((string) $column->Type), 'text')) {
            return;
        }

        DB::statement('ALTER TABLE api_credentials MODIFY secret_key TEXT NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('api_credentials')) {
            return;
        }

        DB::statement('ALTER TABLE api_credentials MODIFY secret_key VARCHAR(255) NOT NULL');
    }
};
