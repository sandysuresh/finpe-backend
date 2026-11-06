<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alliancepe_payouts', function (Blueprint $table) {
            $table->foreignId('commission_rule_id')->nullable()->after('amount')->constrained('commission_rules')->nullOnDelete();
            $table->string('commission_type', 20)->nullable()->after('commission_rule_id');
            $table->decimal('commission_rate', 18, 2)->nullable()->after('commission_type');
            $table->decimal('commission_amount', 18, 2)->default(0)->after('commission_rate');
            $table->decimal('total_debited', 18, 2)->nullable()->after('commission_amount');
        });
    }

    public function down(): void
    {
        Schema::table('alliancepe_payouts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('commission_rule_id');
            $table->dropColumn(['commission_type', 'commission_rate', 'commission_amount', 'total_debited']);
        });
    }
};
