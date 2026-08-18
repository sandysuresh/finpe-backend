<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->unsignedTinyInteger('registration_step')
                ->default(1)
                ->after('password');

            $table->timestamp('registration_completed_at')
                ->nullable()
                ->after('registration_step');

            $table->timestamp('email_verified_at')
                ->nullable()
                ->after('registration_completed_at');

            $table->rememberToken();
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'registration_step',
                'registration_completed_at',
                'email_verified_at',
            ]);

            $table->dropRememberToken();
        });
    }
};