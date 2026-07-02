<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


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
            ->dontSubmitEmptyLogs();
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
            ->withPivot(['scope_id', 'permission_id'])
            ->withTimestamps();
    }
}
