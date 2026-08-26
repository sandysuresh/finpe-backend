<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('banks')) {
            Schema::create('banks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->string('driver')->default('simulator');
                $table->string('environment')->default('sandbox');
                $table->string('base_url')->nullable();
                $table->text('username')->nullable();
                $table->text('password')->nullable();
                $table->text('api_key')->nullable();
                $table->text('api_secret')->nullable();
                $table->json('services')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->timestamp('last_tested_at')->nullable();
                $table->string('last_test_status')->nullable();
                $table->string('last_test_message')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('transactions', 'bank_id')) {
                    $table->foreignId('bank_id')->nullable()->after('vendor_id')->constrained('banks')->nullOnDelete();
                }
                if (! Schema::hasColumn('transactions', 'bank_reference')) {
                    $table->string('bank_reference')->nullable()->after('reference');
                }
                if (! Schema::hasColumn('transactions', 'account_number')) {
                    $table->string('account_number')->nullable()->after('beneficiary_name');
                }
                if (! Schema::hasColumn('transactions', 'ifsc_code')) {
                    $table->string('ifsc_code')->nullable()->after('account_number');
                }
                if (! Schema::hasColumn('transactions', 'bank_name')) {
                    $table->string('bank_name')->nullable()->after('ifsc_code');
                }
                if (! Schema::hasColumn('transactions', 'remarks')) {
                    $table->string('remarks')->nullable()->after('bank_name');
                }
                if (! Schema::hasColumn('transactions', 'channel')) {
                    $table->string('channel')->default('panel')->after('type');
                }
                if (! Schema::hasColumn('transactions', 'failure_reason')) {
                    $table->string('failure_reason')->nullable()->after('status');
                }
            });
        }

        if (Schema::hasTable('banks') && DB::table('banks')->count() === 0) {
            DB::table('banks')->insert([
                'name' => 'Sandbox Bank (Simulator)',
                'code' => 'SANDBOX',
                'driver' => 'simulator',
                'environment' => 'sandbox',
                'services' => json_encode(['imps', 'neft', 'rtgs']),
                'is_active' => true,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                foreach (['failure_reason', 'channel', 'remarks', 'bank_name', 'ifsc_code', 'account_number', 'bank_reference'] as $col) {
                    if (Schema::hasColumn('transactions', $col)) {
                        $table->dropColumn($col);
                    }
                }
                if (Schema::hasColumn('transactions', 'bank_id')) {
                    $table->dropConstrainedForeignId('bank_id');
                }
            });
        }

        Schema::dropIfExists('banks');
    }
};
