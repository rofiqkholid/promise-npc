<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;


use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcRole extends Model
{

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

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
