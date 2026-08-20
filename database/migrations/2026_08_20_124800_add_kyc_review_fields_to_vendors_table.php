<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'kyc_comment')) {
                $table->text('kyc_comment')->nullable()->after('kyc_status');
            }
            if (! Schema::hasColumn('vendors', 'kyc_reviewed_at')) {
                $table->timestamp('kyc_reviewed_at')->nullable()->after('kyc_comment');
            }
            if (! Schema::hasColumn('vendors', 'kyc_reviewed_by')) {
                $table->foreignId('kyc_reviewed_by')
                    ->nullable()
                    ->after('kyc_reviewed_at')
                    ->constrained('admins')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'kyc_reviewed_by')) {
                $table->dropConstrainedForeignId('kyc_reviewed_by');
            }
            if (Schema::hasColumn('vendors', 'kyc_reviewed_at')) {
                $table->dropColumn('kyc_reviewed_at');
            }
            if (Schema::hasColumn('vendors', 'kyc_comment')) {
                $table->dropColumn('kyc_comment');
            }
        });
    }
};
