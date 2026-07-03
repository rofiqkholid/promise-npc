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
            $combinedPivot->can_view = in_array('view', $perms);
            $combinedPivot->can_create = in_array('create', $perms);
            $combinedPivot->can_update = in_array('update', $perms);
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
        if ($this->roles->contains('code', 'admin') || $this->roles->contains('role_name', 'admin')) {
            return true;
        }

        $menus = $this->getAllAccessibleMenus();
        $menu = $menus->firstWhere('route', $routeName);

        if (!$menu) {
            // Also try old route_name if fallback is needed, but we renamed the column to route
            return false;
        }

        $property = 'can_' . $action;
        return $menu->combined_pivot->$property ?? false;
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
        if ($this->roles->contains('code', 'admin')) {
            return true;
        }

        // 1. Lapis Pertama: Cek apakah user punya hak can_approve di menu ini (via Role atau User Specific)
        if (!$this->hasMenuAccess('checksheet-approvals.index', 'approve')) {
            return false;
        }

        // 2. Lapis Kedua: Pastikan Job Title role sesuai dengan tahapan (stage)
        $stageRoleMap = [
            'WAITING_QE_STAFF' => 'qe_staff',
            'WAITING_MGM_STAFF' => 'npc_staff',
            'WAITING_QE_SPV'   => 'qe_asst_mgr',
            'WAITING_MGM_SPV'  => 'npc_asst_mgr',
            'WAITING_QE_MGR'   => 'qe_mgr',
            'WAITING_MGM_MGR'  => 'npc_mgr',
        ];

        // Jika stage tidak ada di map, kembalikan false demi keamanan
        if (!array_key_exists($stage, $stageRoleMap)) {
            return false;
        }

        $requiredRoleCode = $stageRoleMap[$stage];
        
        return $this->roles->contains('code', $requiredRoleCode);
    }
}
