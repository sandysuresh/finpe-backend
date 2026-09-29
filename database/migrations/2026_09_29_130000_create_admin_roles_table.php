<?php

use App\Support\AdminAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admin_roles')) {
            Schema::create('admin_roles', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->string('description')->nullable();
                $table->boolean('is_system')->default(false);
                $table->json('permissions')->nullable();
                $table->timestamps();
            });
        }

        $roles = [
            'super_admin' => 'Super Admin',
            'operations_admin' => 'Operations Admin',
            'finance_admin' => 'Finance Admin',
            'kyc_admin' => 'KYC Admin',
            'support_admin' => 'Support Admin',
            'report_admin' => 'Report Admin',
            'staff' => 'Custom',
        ];

        foreach ($roles as $slug => $name) {
            if (DB::table('admin_roles')->where('slug', $slug)->exists()) {
                continue;
            }

            DB::table('admin_roles')->insert([
                'slug' => $slug,
                'name' => $name,
                'description' => null,
                'is_system' => true,
                'permissions' => $slug === 'super_admin' ? null : json_encode(AdminAccess::preset($slug)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_roles');
    }
};
