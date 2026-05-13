<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\HasHashedId;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
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
        return $this->belongsToMany(\App\Models\NpcRole::class, 'npc_user_roles', 'user_id', 'role_id', 'id');
    }

    public function specificMenus()
    {
        return $this->belongsToMany(\App\Models\NpcMenu::class, 'npc_user_menus', 'user_id', 'menu_id', 'id')
            ->withPivot(['can_view', 'can_create', 'can_update', 'can_delete', 'can_approve'])
            ->withTimestamps();
    }

    /**
     * Get all accessible menus for this user (from roles + specific menus)
     */
    public function getAllAccessibleMenus()
    {
        // Get menus from all roles
        $roleMenus = collect();
        foreach ($this->roles()->with('menus')->get() as $role) {
            $roleMenus = $roleMenus->merge($role->menus);
        }

        // Get specific user menus
        $userMenus = $this->specificMenus()->get();

        // Merge them together. We key by menu ID to avoid duplicates.
        // If there's a duplicate, we should ideally combine the permissions (logical OR).
        $allMenus = $roleMenus->merge($userMenus)->keyBy('id')->map(function ($menu) use ($roleMenus, $userMenus) {
            $roleMenu = $roleMenus->firstWhere('id', $menu->id);
            $userMenu = $userMenus->firstWhere('id', $menu->id);

            // Create a fake pivot object that combines the permissions
            $combinedPivot = new \stdClass();
            $permissions = ['can_view', 'can_create', 'can_update', 'can_delete', 'can_approve'];
            
            foreach ($permissions as $perm) {
                $rolePerm = $roleMenu && $roleMenu->pivot ? $roleMenu->pivot->$perm : false;
                $userPerm = $userMenu && $userMenu->pivot ? $userMenu->pivot->$perm : false;
                $combinedPivot->$perm = $rolePerm || $userPerm;
            }
            
            $menu->setAttribute('combined_pivot', $combinedPivot);
            return $menu;
        });

        return $allMenus->values();
    }

    /**
     * Check if user has access to a specific route and action
     * @param string $routeName
     * @param string $action (view, create, update, delete, approve)
     * @return bool
     */
    public function hasMenuAccess($routeName, $action = 'view')
    {
        // Admin bypass (optional, but good practice)
        if ($this->roles->contains('code', 'admin')) {
            return true;
        }

        $menus = $this->getAllAccessibleMenus();
        $menu = $menus->firstWhere('route_name', $routeName);

        if (!$menu) {
            return false;
        }

        $permColumn = 'can_' . $action;
        return $menu->combined_pivot->$permColumn ?? false;
    }
}
