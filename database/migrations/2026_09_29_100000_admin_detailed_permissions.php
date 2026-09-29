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
        Schema::table('admins', function (Blueprint $table) {
            if (! Schema::hasColumn('admins', 'mobile')) {
                $table->string('mobile', 20)->nullable()->after('email');
            }
            if (! Schema::hasColumn('admins', 'department')) {
                $table->string('department', 80)->nullable()->after('mobile');
            }
            if (! Schema::hasColumn('admins', 'branch_region')) {
                $table->string('branch_region', 80)->nullable()->after('department');
            }
            if (! Schema::hasColumn('admins', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('status');
            }
        });

        if (! Schema::hasColumn('admin_module_permissions', 'action')) {
            Schema::table('admin_module_permissions', function (Blueprint $table) {
                $table->string('action', 40)->nullable()->after('module');
            });
        }

        $indexNames = collect(DB::select('SHOW INDEX FROM admin_module_permissions'))->pluck('Key_name');
        if (! $indexNames->contains('admin_module_permissions_admin_id_index')) {
            Schema::table('admin_module_permissions', function (Blueprint $table) {
                $table->index('admin_id', 'admin_module_permissions_admin_id_index');
            });
        }

        if ($indexNames->contains('admin_module_permissions_admin_id_module_unique')) {
            Schema::table('admin_module_permissions', function (Blueprint $table) {
                $table->dropUnique(['admin_id', 'module']);
            });
        }

        $existing = DB::table('admin_module_permissions')->whereNull('action')->get();
        foreach ($existing as $row) {
            $actions = AdminAccess::actionsFor($row->module);
            if ($actions === []) {
                $actions = ['view'];
            }

            DB::table('admin_module_permissions')->where('id', $row->id)->update([
                'action' => $actions[0],
                'updated_at' => now(),
            ]);

            $extra = [];
            foreach (array_slice($actions, 1) as $action) {
                $extra[] = [
                    'admin_id' => $row->admin_id,
                    'module' => $row->module,
                    'action' => $action,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if ($extra !== []) {
                DB::table('admin_module_permissions')->insert($extra);
            }
        }

        $indexNames = collect(DB::select('SHOW INDEX FROM admin_module_permissions'))->pluck('Key_name');
        if (! $indexNames->contains('admin_module_permissions_admin_id_module_action_unique')) {
            Schema::table('admin_module_permissions', function (Blueprint $table) {
                $table->unique(['admin_id', 'module', 'action']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('admin_module_permissions', function (Blueprint $table) {
            $table->dropUnique(['admin_id', 'module', 'action']);
        });

        DB::table('admin_module_permissions')->where('action', '!=', 'view')->delete();

        Schema::table('admin_module_permissions', function (Blueprint $table) {
            $table->unique(['admin_id', 'module']);
            $table->dropColumn('action');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['mobile', 'department', 'branch_region', 'two_factor_enabled']);
        });
    }
};
