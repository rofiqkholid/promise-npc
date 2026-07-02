<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixNpcRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Add missing columns to roles table
        if (!Schema::hasColumn('roles', 'code')) {
            DB::statement('ALTER TABLE roles ADD code NVARCHAR(50) NULL');
            $this->command->info('Added column: code');
        }
        if (!Schema::hasColumn('roles', 'name')) {
            DB::statement('ALTER TABLE roles ADD name NVARCHAR(100) NULL');
            $this->command->info('Added column: name');
        }

        // 2. Update the NPC roles with their proper codes and names
        $updates = [
            'qc' => ['name' => 'Quality Control', 'code' => 'qc'],
            'NPC' => ['name' => 'Admin NPC', 'code' => 'NPC'],
            'qe_staff' => ['name' => 'QE Staff/SPV', 'code' => 'qe_staff'],
            'npc_staff' => ['name' => 'NPC Staff/SPV', 'code' => 'npc_staff'],
            'qe_asst_mgr' => ['name' => 'QE Asst Mgr', 'code' => 'qe_asst_mgr'],
            'npc_asst_mgr' => ['name' => 'NPC Asst Mgr', 'code' => 'npc_asst_mgr'],
            'qe_mgr' => ['name' => 'QE Mgr', 'code' => 'qe_mgr'],
            'npc_mgr' => ['name' => 'NPC Mgr', 'code' => 'npc_mgr'],
        ];

        foreach ($updates as $roleName => $data) {
            DB::table('roles')
                ->where('role_name', $roleName)
                ->where('scope_id', 'app_npc')
                ->update([
                    'code' => $data['code'],
                    'name' => $data['name']
                ]);
        }
        $this->command->info('Updated existing NPC roles names & codes.');

        // 3. Ensure Admin and Operator exist for app_npc
        $adminId = null;
        $adminRole = DB::table('roles')->where('code', 'admin')->where('scope_id', 'app_npc')->first();
        if (!$adminRole) {
            $adminId = DB::table('roles')->insertGetId([
                'role_name' => 'Administrator',
                'code' => 'admin',
                'name' => 'Administrator',
                'scope_id' => 'app_npc',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->command->info('Created dedicated Administrator role for app_npc.');
        } else {
            $adminId = $adminRole->id;
        }

        $operatorRole = DB::table('roles')->where('code', 'operator')->where('scope_id', 'app_npc')->first();
        if (!$operatorRole) {
            DB::table('roles')->insert([
                'role_name' => 'Production Operator',
                'code' => 'operator',
                'name' => 'Production Operator',
                'scope_id' => 'app_npc',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->command->info('Created dedicated Production Operator role for app_npc.');
        }

        // 4. Migrate users from shared Admin to new Administrator
        $oldAdminRole = DB::table('roles')->where('role_name', 'Admin')->whereNull('scope_id')->first();
        if ($adminId && $oldAdminRole) {
            $affected = DB::table('user_scope_roles')
                ->where('role_id', $oldAdminRole->id)
                ->where('scope_id', 'app_npc')
                ->update(['role_id' => $adminId]);
            
            if ($affected > 0) {
                $this->command->info("Migrated {$affected} users from shared Admin to NPC Administrator.");
            }
        }

        $this->command->info('Fix completed successfully!');
    }
}
