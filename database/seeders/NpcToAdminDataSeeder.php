<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NpcToAdminDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting data migration from npc_ tables to admin tables via Seeder...');

        DB::transaction(function () {
            // ==========================================
            // 0. CLEANUP PREVIOUS MIGRATED DATA
            // ==========================================
            $this->command->info('Cleaning up previously migrated data...');
            
            // Delete user_scope_permissions, role_scope_permissions, user_scope_roles
            if (Schema::hasTable('user_scope_permissions')) {
                DB::table('user_scope_permissions')->where('scope_id', 'app_npc')->delete();
            }
            if (Schema::hasTable('role_scope_permissions')) {
                DB::table('role_scope_permissions')->where('scope_id', 'app_npc')->delete();
            }
            if (Schema::hasTable('user_scope_roles')) {
                DB::table('user_scope_roles')->where('scope_id', 'app_npc')->delete();
            }

            // Delete roles
            if (Schema::hasColumn('roles', 'scope_id')) {
                DB::table('roles')->where('scope_id', 'app_npc')->delete();
            }

            // Delete menus
            if (Schema::hasColumn('menus', 'scope_id')) {
                DB::table('menus')->where('scope_id', 'app_npc')->delete();
            }

            // ==========================================
            // 1. MIGRATE MENUS
            // ==========================================
            $this->command->info('Migrating menus...');
            $npcMenus = DB::table('npc_menus')->orderBy('id')->get();
            $menuMap = []; // old_id => new_id

            foreach ($npcMenus as $menu) {
                $data = (array) $menu;
                $oldId = $data['id'];
                unset($data['id']); 
                
                $oldParentId = $data['parent_id'];
                $data['parent_id'] = null;
                
                $newId = DB::table('menus')->insertGetId($data);
                $menuMap[$oldId] = [
                    'new_id' => $newId,
                    'old_parent_id' => $oldParentId,
                ];
            }

            // Update parent_id for migrated menus
            foreach ($menuMap as $oldId => $mapData) {
                if ($mapData['old_parent_id']) {
                    $newParentId = $menuMap[$mapData['old_parent_id']]['new_id'] ?? null;
                    if ($newParentId) {
                        DB::table('menus')
                            ->where('id', $mapData['new_id'])
                            ->update(['parent_id' => $newParentId]);
                    }
                }
            }
            $this->command->info('Migrated ' . count($npcMenus) . ' menus.');

            // ==========================================
            // 2. MIGRATE ROLES
            // ==========================================
            $this->command->info('Migrating roles...');
            $npcRoles = DB::table('npc_roles')->orderBy('id')->get();
            $roleMap = []; // old_id => new_id

            foreach ($npcRoles as $role) {
                $rolesHasCode = Schema::hasColumn('roles', 'code');
                $roleName = $role->code ?? $role->name;
                
                // Check if role already exists in target table
                $existingRole = null;
                if ($rolesHasCode) {
                    $existingRole = DB::table('roles')->where('code', $roleName)->first();
                } else {
                    $existingRole = DB::table('roles')->where('role_name', $roleName)->first();
                }

                if ($existingRole) {
                    // Role already exists, map to existing ID
                    $roleMap[$role->id] = $existingRole->id;
                    $this->command->info("Role '{$roleName}' already exists. Mapped to ID {$existingRole->id}.");
                } else {
                    $data = [];
                    if ($rolesHasCode) {
                        $data = (array) $role;
                        unset($data['id']);
                    } else {
                        $data['role_name'] = $roleName;
                        if (Schema::hasColumn('roles', 'scope_id')) {
                            $data['scope_id'] = $role->scope_id ?? 'app_npc';
                        }
                        $data['created_at'] = $role->created_at;
                        $data['updated_at'] = $role->updated_at;
                    }
                    
                    $newId = DB::table('roles')->insertGetId($data);
                    $roleMap[$role->id] = $newId;
                }
            }
            $this->command->info('Migrated/Mapped ' . count($npcRoles) . ' roles.');

            // ==========================================
            // 3. MIGRATE USER_ROLES -> user_scope_roles
            // ==========================================
            $this->command->info('Migrating user_roles...');
            $npcUserRoles = DB::table('npc_user_roles')->get();
            $userRoleCount = 0;
            foreach ($npcUserRoles as $ur) {
                if (isset($roleMap[$ur->role_id])) {
                    // Cek apakah data duplicate sudah ada di user_scope_roles
                    $exists = DB::table('user_scope_roles')
                        ->where('user_id', $ur->user_id)
                        ->where('scope_id', 'app_npc')
                        ->where('role_id', $roleMap[$ur->role_id])
                        ->exists();

                    if (!$exists) {
                        DB::table('user_scope_roles')->insert([
                            'user_id' => $ur->user_id,
                            'scope_id' => 'app_npc',
                            'role_id' => $roleMap[$ur->role_id]
                        ]);
                        $userRoleCount++;
                    }
                }
            }
            $this->command->info('Migrated ' . $userRoleCount . ' user_scope_roles.');

            // ==========================================
            // 4. MIGRATE ROLE_MENUS -> role_scope_permissions
            // ==========================================
            $this->command->info('Migrating role_menus...');
            $npcRoleMenus = DB::table('npc_role_menus')->get();
            $roleMenuCount = 0;
            
            foreach ($npcRoleMenus as $rm) {
                if (isset($roleMap[$rm->role_id]) && isset($menuMap[$rm->menu_id])) {
                    $exists = DB::table('role_scope_permissions')
                        ->where('role_id', $roleMap[$rm->role_id])
                        ->where('scope_id', $rm->scope_id ?? 'app_npc')
                        ->where('menu_id', $menuMap[$rm->menu_id]['new_id'])
                        ->where('permission_id', $rm->permission_id)
                        ->exists();
                        
                    if (!$exists) {
                        DB::table('role_scope_permissions')->insert([
                            'role_id' => $roleMap[$rm->role_id],
                            'scope_id' => $rm->scope_id ?? 'app_npc',
                            'menu_id' => $menuMap[$rm->menu_id]['new_id'],
                            'permission_id' => $rm->permission_id,
                        ]);
                        $roleMenuCount++;
                    }
                }
            }
            $this->command->info('Migrated ' . $roleMenuCount . ' role_scope_permissions.');

            // ==========================================
            // 5. MIGRATE USER_MENUS -> user_scope_permissions
            // ==========================================
            $this->command->info('Migrating user_menus...');
            $npcUserMenus = DB::table('npc_user_menus')->get();
            $userMenuCount = 0;
            
            foreach ($npcUserMenus as $um) {
                if (isset($menuMap[$um->menu_id])) {
                    $exists = DB::table('user_scope_permissions')
                        ->where('user_id', $um->user_id)
                        ->where('scope_id', $um->scope_id ?? 'app_npc')
                        ->where('menu_id', $menuMap[$um->menu_id]['new_id'])
                        ->where('permission_id', $um->permission_id)
                        ->exists();

                    if (!$exists) {
                        DB::table('user_scope_permissions')->insert([
                            'user_id' => $um->user_id,
                            'scope_id' => $um->scope_id ?? 'app_npc',
                            'menu_id' => $menuMap[$um->menu_id]['new_id'],
                            'permission_id' => $um->permission_id,
                            'access_type' => $um->access_type ?? 'ALLOW',
                        ]);
                        $userMenuCount++;
                    }
                }
            }
            $this->command->info('Migrated ' . $userMenuCount . ' user_scope_permissions.');

        });
        
        $this->command->info('Seeding/Migration completed successfully!');
    }
}
