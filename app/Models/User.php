<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\HasHashedId;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    use HasHashedId;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'nik';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'nik',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getAuthIdentifierName()
    {
        return 'nik';
    }

    public function roles()
    {
        return $this->belongsToMany(\App\Models\NpcRole::class, 'user_scope_roles', 'user_id', 'role_id', 'id', 'id')
                    ->withoutGlobalScope('scope_npc')
                    ->withPivotValue('scope_id', 'app_npc');
    }

    public function specificMenus()
    {
        return $this->belongsToMany(\App\Models\NpcMenu::class, 'user_scope_permissions', 'user_id', 'menu_id', 'id', 'id')
            ->withPivot(['permission_id', 'access_type'])
            ->withPivotValue('scope_id', 'app_npc');
    }

    public function getAllAccessibleMenus()
    {
        // 1. Get all roles the user has
        $roleIds = $this->roles()->pluck('roles.id')->toArray();

        // 2. Query role_scope_permissions
        $rolePermissions = \Illuminate\Support\Facades\DB::table('role_scope_permissions')
            ->join('permissions', 'role_scope_permissions.permission_id', '=', 'permissions.id')
            ->whereIn('role_id', $roleIds)
            ->where('role_scope_permissions.scope_id', 'app_npc')
            ->get(['menu_id', 'permission_name']);

        // 3. Query user_scope_permissions if exists
        $userPermissions = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('user_scope_permissions')) {
            $userPermissions = \Illuminate\Support\Facades\DB::table('user_scope_permissions')
                ->join('permissions', 'user_scope_permissions.permission_id', '=', 'permissions.id')
                ->where('user_id', $this->id)
                ->where('user_scope_permissions.scope_id', 'app_npc')
                ->where('access_type', 'ALLOW')
                ->get(['menu_id', 'permission_name']);
        }

        // 4. Combine permissions by menu_id
        $menuPerms = [];
        foreach ($rolePermissions as $rp) {
            $menuPerms[$rp->menu_id][] = $rp->permission_name;
        }
        foreach ($userPermissions as $up) {
            $menuPerms[$up->menu_id][] = $up->permission_name;
        }

        // 5. Fetch all related Menus
        if (empty($menuPerms)) {
            return collect();
        }

        $menus = \App\Models\NpcMenu::whereIn('id', array_keys($menuPerms))
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // 6. Map the permissions into the combined_pivot for backwards compatibility
        return $menus->map(function ($menu) use ($menuPerms) {
            $combinedPivot = new \stdClass();
            $perms = $menuPerms[$menu->id] ?? [];
            $combinedPivot->permissions = $perms;
            $combinedPivot->can_view = in_array('view', $perms);
            $combinedPivot->can_create = in_array('create', $perms);
            $combinedPivot->can_update = in_array('update', $perms) || in_array('edit', $perms);
            $combinedPivot->can_edit = in_array('edit', $perms) || in_array('update', $perms);
            $combinedPivot->can_delete = in_array('delete', $perms);
            $combinedPivot->can_approve = in_array('approve', $perms);
            
            $menu->setAttribute('combined_pivot', $combinedPivot);
            return $menu;
        });
    }

    /**
     * Check if user has access to a specific route and action
     * @param string $routeName
     * @param string $action (view, create, update, delete, approve)
     * @return bool
     */
    public function hasMenuAccess($routeName, $action = 'view')
    {
        // Admin bypass
        if ($this->roles->contains('code', 'admin') || $this->roles->contains('code', 'npc_admin') || $this->roles->contains('role_name', 'admin')) {
            return true;
        }

        $menus = $this->getAllAccessibleMenus();
        $menu = $menus->firstWhere('route', $routeName);

        if (!$menu) {
            $menu = $menus->firstWhere('route_name', $routeName);
        }

        if (!$menu) {
            $menu = $menus->first(function($m) use ($routeName) {
                return ($m->route ?? $m->route_name ?? '') === $routeName 
                    || str_contains(strtolower($m->title ?? ''), strtolower($routeName));
            });
        }

        if (!$menu) {
            return false;
        }

        $property = 'can_' . $action;
        if ($menu->combined_pivot->$property ?? false) {
            return true;
        }

        // Direct check in raw permissions array (supports 'edit', 'delete', 'upload', 'download', etc.)
        if (!empty($menu->combined_pivot->permissions) && in_array($action, $menu->combined_pivot->permissions, true)) {
            return true;
        }

        // If checking for 'view', having any higher permission (approve, update, create, delete) implicitly grants view
        if ($action === 'view') {
            return ($menu->combined_pivot->can_approve ?? false)
                || ($menu->combined_pivot->can_update ?? false)
                || ($menu->combined_pivot->can_create ?? false)
                || ($menu->combined_pivot->can_delete ?? false);
        }

        return false;
    }

    /**
     * Check if user is authorized to approve a specific checksheet stage.
     * Requires the user to have BOTH the can_approve permission AND the matching Job Title role.
     * @param string $stage
     * @return bool
     */
    public function canApproveChecksheetStage($stage)
    {
        // Admin bypass
        if ($this->roles->contains('code', 'admin') || $this->roles->contains('code', 'npc_admin')) {
            return true;
        }

        // 1. Lapis Pertama: Cek apakah user punya hak can_approve di menu ini (via Role atau User Specific)
        if (!$this->hasMenuAccess('checksheet-approvals.index', 'approve')) {
            return false;
        }

        // 2. Lapis Kedua: Pastikan Job Title role sesuai dengan tahapan (stage)
        $stageRoleMap = [
            'WAITING_QE_STAFF'   => ['qe_staff', 'qestaff'],
            'WAITING_MGM_STAFF'  => ['npc_staff', 'npcstaff', 'mgm_staff', 'mgmstaff'],
            'WAITING_QE_SPV'     => ['qe_spv', 'qespv', 'qe_staff', 'qestaff'],
            'WAITING_MGM_SPV'    => ['npc_spv', 'npcspv', 'npc_staff', 'npcstaff', 'mgm_spv', 'mgmspv'],
            'WAITING_QE_ASSMAN'  => ['qe_asst_mgr', 'qeasstmgr', 'qe_assman', 'qeassman'],
            'WAITING_MGM_ASSMAN' => ['npc_asst_mgr', 'npcasstmgr', 'npc_assman', 'npcassman'],
            'WAITING_QE_MGR'     => ['qe_mgr', 'qemgr'],
            'WAITING_MGM_MGR'    => ['npc_mgr', 'npcmgr', 'mgm_mgr', 'mgmmgr'],
        ];

        // Jika stage tidak ada di map, kembalikan false demi keamanan
        if (!array_key_exists($stage, $stageRoleMap)) {
            return false;
        }

        $targets = (array) $stageRoleMap[$stage];
        
        return $this->roles->contains(function ($role) use ($targets) {
            $codeNorm = strtolower(str_replace([' ', '_', '-'], '', $role->code ?? ''));
            $nameNorm = strtolower(str_replace([' ', '_', '-'], '', $role->name ?? $role->role_name ?? ''));
            foreach ($targets as $target) {
                $targetNorm = strtolower(str_replace([' ', '_', '-'], '', $target));
                if (str_contains($codeNorm, $targetNorm) || str_contains($nameNorm, $targetNorm)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Check if user is the root system administrator.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        $identifier = strtolower(trim($this->nik ?? $this->username ?? $this->name ?? ''));
        return $identifier === 'administrator' || $this->id === 1;
    }

    /**
     * Helper to check if user has admin privileges.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user has explicit permission for a route and action strictly
     * based on their role/user override permissions without role-bypass.
     *
     * @param string $routeName
     * @param string $action (view, create, update, delete, approve, etc.)
     * @return bool
     */
    public function hasMenuPermissionStrict(string $routeName, string $action = 'delete'): bool
    {
        $menus = $this->getAllAccessibleMenus();
        $menu = $menus->firstWhere('route', $routeName);

        if (!$menu) {
            $menu = $menus->firstWhere('route_name', $routeName);
        }

        if (!$menu) {
            $menu = $menus->first(function($m) use ($routeName) {
                return ($m->route ?? $m->route_name ?? '') === $routeName 
                    || str_contains(strtolower($m->title ?? ''), strtolower($routeName));
            });
        }

        if (!$menu || !isset($menu->combined_pivot)) {
            return false;
        }

        // 1. Direct check in raw permissions array (supports 'edit', 'delete', 'upload', 'download', etc.)
        if (!empty($menu->combined_pivot->permissions) && in_array($action, $menu->combined_pivot->permissions, true)) {
            return true;
        }

        // 2. Check in named property (can_delete, can_update, can_create, can_approve, can_view)
        $property = 'can_' . $action;
        return (bool) ($menu->combined_pivot->$property ?? false);
    }

    /**
     * Check if user is authorized to reject a checksheet directly to Quality Check (QC).
     *
     * 1. Default: Hanya akun Super Administrator sistem (username/nik 'administrator') yang otomatis memiliki akses.
     * 2. User lain (termasuk role Admin NPC, SPV, Mgr, Staff, dsb): Wajib dicek hak aksesnya dari
     *    Override Permissions pada menu Checksheet Approvals dengan action 'delete'.
     *
     * @param \App\Models\NpcChecksheet|null $checksheet
     * @return bool
     */
    public function canRejectChecksheetToQc(?\App\Models\NpcChecksheet $checksheet = null): bool
    {
        // 1. Default: Hanya akun Administrator utama sistem yang bypass
        if ($this->isSuperAdmin()) {
            return true;
        }

        // 2. Untuk user lain (termasuk role Admin NPC), dicek secara ketat dari Override Permissions di Promise Admin
        $action = config('npc.reject_to_qc.action', 'delete');

        return $this->hasMenuPermissionStrict('checksheet-approvals.index', $action);
    }
}
