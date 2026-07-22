<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $parent = \Illuminate\Support\Facades\DB::table('menus')
            ->whereNull('parent_id')
            ->where('title', 'Master Data')
            ->where('scope_id', 'app_npc')
            ->first();

        // Prevent duplicate if it exists from previous dirty state
        $existing = \Illuminate\Support\Facades\DB::table('menus')
            ->where('route', 'master.checksheet_approvals.index')
            ->where('scope_id', 'app_npc')
            ->first();
            
        if ($existing) {
            $menuId = $existing->id;
        } else {
            $menuId = \Illuminate\Support\Facades\DB::table('menus')->insertGetId([
                'scope_id' => 'app_npc',
                'parent_id' => $parent ? $parent->id : null,
                'title' => 'Master CS Approvals',
                'route' => 'master.checksheet_approvals.index',
                'icon' => 'fa-solid fa-clipboard-check',
                'sort_order' => 99,
                'level' => 1,
                'is_active' => true,
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Get permissions
        $viewPerm = \Illuminate\Support\Facades\DB::table('permissions')->where('permission_name', 'view')->first();
        $approvePerm = \Illuminate\Support\Facades\DB::table('permissions')->where('permission_name', 'approve')->first();

        // Get roles that should have access (admin, npc_admin, qe_mgr, qe_spv)
        $roles = \Illuminate\Support\Facades\DB::table('roles')
            ->whereIn('code', ['admin', 'npc_admin', 'qe_mgr', 'qe_spv'])
            ->where('scope_id', 'app_npc')
            ->get();

        $roleMenuData = [];
        foreach ($roles as $role) {
            if ($viewPerm) {
                // Check if exists
                $exists = \Illuminate\Support\Facades\DB::table('role_scope_permissions')
                    ->where('role_id', $role->id)
                    ->where('menu_id', $menuId)
                    ->where('permission_id', $viewPerm->id)
                    ->exists();
                if (!$exists) {
                    $roleMenuData[] = [
                        'role_id' => $role->id,
                        'scope_id' => 'app_npc',
                        'menu_id' => $menuId,
                        'permission_id' => $viewPerm->id,
                    ];
                }
            }
            if ($approvePerm) {
                $exists = \Illuminate\Support\Facades\DB::table('role_scope_permissions')
                    ->where('role_id', $role->id)
                    ->where('menu_id', $menuId)
                    ->where('permission_id', $approvePerm->id)
                    ->exists();
                if (!$exists) {
                    $roleMenuData[] = [
                        'role_id' => $role->id,
                        'scope_id' => 'app_npc',
                        'menu_id' => $menuId,
                        'permission_id' => $approvePerm->id,
                    ];
                }
            }
        }

        if (!empty($roleMenuData)) {
            \Illuminate\Support\Facades\DB::table('role_scope_permissions')->insert($roleMenuData);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $menu = \Illuminate\Support\Facades\DB::table('menus')
            ->where('route', 'master.checksheet_approvals.index')
            ->where('scope_id', 'app_npc')
            ->first();

        if ($menu) {
            \Illuminate\Support\Facades\DB::table('role_scope_permissions')->where('menu_id', $menu->id)->delete();
            \Illuminate\Support\Facades\DB::table('menus')->where('id', $menu->id)->delete();
        }
    }
};
