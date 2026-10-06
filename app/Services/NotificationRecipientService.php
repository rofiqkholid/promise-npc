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

        // Query users based on role matching stage (e.g. SECTION_HEAD, DEPT_HEAD, DIV_HEAD, QA_QC)
        $roles = \App\Models\NpcRole::where('name', 'like', "%{$stage}%")->pluck('id');
        if ($roles->isNotEmpty()) {
            $userNiks = \Illuminate\Support\Facades\DB::table('user_scope_roles')
                ->whereIn('role_id', $roles)
                ->pluck('user_id');

            $emails = User::whereIn('nik', $userNiks)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->pluck('email')
                ->toArray();
        }

        return array_values(array_unique(array_filter($emails, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        })));
    }
}
