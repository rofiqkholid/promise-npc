<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Alter npc_roles
        Schema::table('npc_roles', function (Blueprint $table) {
            $table->string('scope_id', 50)->nullable()->after('id');
        });
        DB::table('npc_roles')->update(['scope_id' => 'app_npc']);
        
        // 2. Alter npc_menus
        Schema::table('npc_menus', function (Blueprint $table) {
            $table->string('scope_id', 50)->nullable()->after('id');
            $table->integer('level')->default(1)->after('order');
            $table->boolean('is_visible')->default(true)->after('is_active');
        });
        Schema::table('npc_menus', function (Blueprint $table) {
            $table->renameColumn('route_name', 'route');
            $table->renameColumn('order', 'sort_order');
        });
        DB::table('npc_menus')->update(['scope_id' => 'app_npc']);

        // 3. Alter npc_user_roles
        Schema::table('npc_user_roles', function (Blueprint $table) {
            $table->string('scope_id', 50)->nullable()->after('user_id');
        });
        DB::table('npc_user_roles')->update(['scope_id' => 'app_npc']);

        // 4. Alter npc_role_menus
        Schema::table('npc_role_menus', function (Blueprint $table) {
            $table->string('scope_id', 50)->nullable()->after('role_id');
            $table->integer('permission_id')->nullable()->after('menu_id');
        });

        // 5. Alter npc_user_menus
        if (Schema::hasTable('npc_user_menus')) {
            Schema::table('npc_user_menus', function (Blueprint $table) {
                $table->string('scope_id', 50)->nullable()->after('user_id');
                $table->integer('permission_id')->nullable()->after('menu_id');
                $table->string('access_type', 10)->nullable()->after('permission_id');
            });
        }

        // Transform boolean columns to rows for npc_role_menus and npc_user_menus
        $this->transformPermissions();

        // Drop boolean columns
        Schema::table('npc_role_menus', function (Blueprint $table) {
            $table->dropColumn(['can_view', 'can_create', 'can_update', 'can_delete', 'can_approve']);
        });

        if (Schema::hasTable('npc_user_menus')) {
            Schema::table('npc_user_menus', function (Blueprint $table) {
                $table->dropColumn(['can_view', 'can_create', 'can_update', 'can_delete', 'can_approve']);
            });
        }
    }

    private function transformPermissions()
    {
        $actions = ['view', 'create', 'update', 'delete', 'approve'];
        $permMap = [];
        
        foreach ($actions as $action) {
            $perm = DB::table('permissions')->where('permission_name', $action)->first();
            if (!$perm) {
                $id = DB::table('permissions')->insertGetId([
                    'permission_name' => $action,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $permMap[$action] = $id;
            } else {
                $permMap[$action] = $perm->id;
            }
        }

        // Transform npc_role_menus
        $roleMenus = DB::table('npc_role_menus')->get();
        $newRoleMenus = [];
        foreach ($roleMenus as $rm) {
            $toInsert = [];
            if ($rm->can_view) $toInsert[] = 'view';
            if ($rm->can_create) $toInsert[] = 'create';
            if ($rm->can_update) $toInsert[] = 'update';
            if ($rm->can_delete) $toInsert[] = 'delete';
            if ($rm->can_approve) $toInsert[] = 'approve';

            foreach ($toInsert as $act) {
                $newRoleMenus[] = [
                    'role_id' => $rm->role_id,
                    'scope_id' => 'app_npc',
                    'menu_id' => $rm->menu_id,
                    'permission_id' => $permMap[$act],
                    'created_at' => $rm->created_at,
                    'updated_at' => $rm->updated_at,
                ];
            }
        }
        DB::table('npc_role_menus')->truncate();
        if (count($newRoleMenus) > 0) {
            DB::table('npc_role_menus')->insert($newRoleMenus);
        }

        // Transform npc_user_menus if exists
        if (Schema::hasTable('npc_user_menus')) {
            $userMenus = DB::table('npc_user_menus')->get();
            $newUserMenus = [];
            foreach ($userMenus as $um) {
                $toInsert = [];
                if ($um->can_view) $toInsert[] = 'view';
                if ($um->can_create) $toInsert[] = 'create';
                if ($um->can_update) $toInsert[] = 'update';
                if ($um->can_delete) $toInsert[] = 'delete';
                if ($um->can_approve) $toInsert[] = 'approve';

                foreach ($toInsert as $act) {
                    $newUserMenus[] = [
                        'user_id' => $um->user_id,
                        'scope_id' => 'app_npc',
                        'menu_id' => $um->menu_id,
                        'permission_id' => $permMap[$act],
                        'access_type' => 'ALLOW',
                        'created_at' => $um->created_at,
                        'updated_at' => $um->updated_at,
                    ];
                }
            }
            DB::table('npc_user_menus')->truncate();
            if (count($newUserMenus) > 0) {
                DB::table('npc_user_menus')->insert($newUserMenus);
            }
        }
    }

    public function down(): void
    {
        Schema::table('npc_roles', function (Blueprint $table) {
            $table->dropColumn('scope_id');
        });

        Schema::table('npc_menus', function (Blueprint $table) {
            $table->dropColumn(['scope_id', 'level', 'is_visible']);
        });
        Schema::table('npc_menus', function (Blueprint $table) {
            $table->renameColumn('route', 'route_name');
            $table->renameColumn('sort_order', 'order');
        });

        Schema::table('npc_user_roles', function (Blueprint $table) {
            $table->dropColumn('scope_id');
        });

        Schema::table('npc_role_menus', function (Blueprint $table) {
            $table->boolean('can_view')->default(true);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_approve')->default(false);
            $table->dropColumn(['scope_id', 'permission_id']);
        });

        if (Schema::hasTable('npc_user_menus')) {
            Schema::table('npc_user_menus', function (Blueprint $table) {
                $table->boolean('can_view')->default(true);
                $table->boolean('can_create')->default(false);
                $table->boolean('can_update')->default(false);
                $table->boolean('can_delete')->default(false);
                $table->boolean('can_approve')->default(false);
                $table->dropColumn(['scope_id', 'permission_id', 'access_type']);
            });
        }
    }
};
