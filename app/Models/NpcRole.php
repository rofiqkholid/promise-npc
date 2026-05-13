<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcRole extends Model
{
    use HasHashedId;

    protected $fillable = ['code', 'name', 'description'];

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'npc_user_roles', 'role_id', 'user_id', 'id', 'id');
    }

    public function menus()
    {
        return $this->belongsToMany(\App\Models\NpcMenu::class, 'npc_role_menus', 'role_id', 'menu_id')
            ->withPivot(['can_view', 'can_create', 'can_update', 'can_delete', 'can_approve'])
            ->withTimestamps();
    }
}
