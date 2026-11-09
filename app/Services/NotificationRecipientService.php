<?php

namespace App\Services;

use App\Models\NpcPartProcess;
use App\Models\NpcDepartment;
use App\Models\NpcChecksheet;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationRecipientService
{
    /**
     * Resolve email recipients for the next production process step.
     *
     * @param NpcPartProcess|null $nextProcess
     * @return array<string> Array of recipient email addresses
     */
    public static function getRecipientsForNextProcess(?NpcPartProcess $nextProcess): array
    {
        if (!$nextProcess) {
            return [];
        }

        $department = $nextProcess->department;
        if (!$department) {
            return [];
        }

        $emails = [];

        $deptColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'id_dept') ? 'id_dept' : 'department_id';

        // Filter ONLY users who have access to PROMISE NPC application (scope_id = 'app_npc')
        $npcUserIds = \Illuminate\Support\Facades\DB::table('user_scope_roles')
            ->where('scope_id', 'app_npc')
            ->pluck('user_id')
            ->merge(
                \Illuminate\Support\Facades\DB::table('user_scope_permissions')
                    ->where('scope_id', 'app_npc')
                    ->pluck('user_id')
            )
            ->unique()
            ->filter()
            ->toArray();

        // 1. If sso_department_id is mapped on NpcDepartment
        if (!empty($department->sso_department_id)) {
            $emails = User::where($deptColumn, $department->sso_department_id)
                ->whereIn('id', $npcUserIds)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->pluck('email')
                ->toArray();
        }

        // 2. Fallback: try finding users in SSO departments table matching name
        if (empty($emails)) {
            $ssoDept = \Illuminate\Support\Facades\DB::table('departments')
                ->where('name', 'like', "%{$department->name}%")
                ->orWhere('code', 'like', "%{$department->name}%")
                ->first();

            if ($ssoDept) {
                $emails = User::where($deptColumn, $ssoDept->id)
                    ->whereIn('id', $npcUserIds)
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->pluck('email')
                    ->toArray();
            }
        }

        // Clean & unique emails
        return array_values(array_unique(array_filter($emails, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        })));
    }

    /**
     * Resolve email recipients for Checksheet Approval stages.
     *
     * @param NpcChecksheet $checksheet
     * @param string $stage
     * @return array<string>
     */
    public static function getRecipientsForApproval(NpcChecksheet $checksheet, string $stage): array
    {
        $emails = [];

        // Pemetaan stage approval ke variasi kode / kata kunci role (fleksibel spasi, underscore, kapital)
        $stageRoleMap = [
            'WAITING_QE_STAFF'   => ['qe_staff', 'qe staff'],
            'WAITING_MGM_STAFF'  => ['npc_staff', 'npc staff', 'mgm_staff', 'mgm staff'],
            'WAITING_QE_SPV'     => ['qe_spv', 'qe spv', 'qe_staff', 'qe staff'],
            'WAITING_MGM_SPV'    => ['npc_spv', 'npc spv', 'npc_staff', 'npc staff', 'mgm_spv', 'mgm spv'],
            'WAITING_QE_ASSMAN'  => ['qe_asst_mgr', 'qe asst mgr', 'qe_assman', 'qe assman'],
            'WAITING_MGM_ASSMAN' => ['npc_asst_mgr', 'npc asst mgr', 'npc_assman', 'npc assman'],
            'WAITING_QE_MGR'     => ['qe_mgr', 'qe mgr'],
            'WAITING_MGM_MGR'    => ['npc_mgr', 'npc mgr', 'mgm_mgr', 'mgm mgr'],
        ];

        $targetKeywords = $stageRoleMap[$stage] ?? [];

        if (!empty($targetKeywords)) {
            $roles = \App\Models\NpcRole::where(function($q) use ($targetKeywords) {
                foreach ($targetKeywords as $kw) {
                    $cleanPattern = '%' . str_replace([' ', '_', '-'], '%', $kw) . '%';
                    $q->orWhere('code', 'like', $cleanPattern)
                      ->orWhere('role_name', 'like', $cleanPattern)
                      ->orWhere('name', 'like', $cleanPattern);
                }
            })->pluck('id');

            if ($roles->isNotEmpty()) {
                $userIds = \Illuminate\Support\Facades\DB::table('user_scope_roles')
                    ->whereIn('role_id', $roles)
                    ->pluck('user_id');

                $emails = User::whereIn('id', $userIds)
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->pluck('email')
                    ->toArray();
            }
        }

        return array_values(array_unique(array_filter($emails, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        })));
    }
}
