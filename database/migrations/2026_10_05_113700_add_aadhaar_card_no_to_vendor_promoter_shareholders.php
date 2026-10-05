<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('vendor_promoter_shareholders', 'aadhaar_card_no')) {
            return;
        }

        Schema::table('vendor_promoter_shareholders', function (Blueprint $table) {
            $table->string('aadhaar_card_no', 12)->nullable()->after('pan_card_no');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('vendor_promoter_shareholders', 'aadhaar_card_no')) {
            return;
        }

        Schema::table('vendor_promoter_shareholders', function (Blueprint $table) {
            $table->dropColumn('aadhaar_card_no');
        });
    }
};
