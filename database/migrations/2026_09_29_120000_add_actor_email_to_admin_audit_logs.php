<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('admin_audit_logs', 'actor_email')) {
            Schema::table('admin_audit_logs', function (Blueprint $table) {
                $table->string('actor_email')->nullable()->after('actor_name');
            });
        }

        DB::table('admin_audit_logs')
            ->where('actor_type', 'admin')
            ->whereNull('actor_email')
            ->whereNotNull('actor_id')
            ->orderBy('id')
            ->each(function ($log) {
                $email = DB::table('admins')->where('id', $log->actor_id)->value('email');
                if (is_string($email) && $email !== '') {
                    DB::table('admin_audit_logs')->where('id', $log->id)->update(['actor_email' => $email]);
                }
            });

        DB::table('admin_audit_logs')
            ->where('actor_type', 'vendor')
            ->whereNull('actor_email')
            ->whereNotNull('actor_id')
            ->orderBy('id')
            ->each(function ($log) {
                $email = DB::table('vendors')->where('id', $log->actor_id)->value('email');
                if (is_string($email) && $email !== '') {
                    DB::table('admin_audit_logs')->where('id', $log->id)->update(['actor_email' => $email]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('admin_audit_logs', 'actor_email')) {
            Schema::table('admin_audit_logs', function (Blueprint $table) {
                $table->dropColumn('actor_email');
            });
        }
    }
};
