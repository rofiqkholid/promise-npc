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

    protected $table = 'roles';

    protected static function booted()
    {
        static::addGlobalScope('scope_npc', function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->where($builder->getModel()->getTable() . '.scope_id', 'app_npc');
        });

        static::creating(function ($model) {
            if (empty($model->scope_id)) {
                $model->scope_id = 'app_npc';
            }
        });
    }

    protected $fillable = ['code', 'role_name', 'name', 'description', 'scope_id'];

    public function getNameAttribute($value)
    {
        return $value ?? $this->role_name;
    }

    public function getCodeAttribute($value)
    {
        return $value ?? $this->role_name;
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'user_scope_roles', 'role_id', 'user_id', 'id', 'id')
                    ->wherePivot('scope_id', 'app_npc');
    }

    public function menus()
    {
        return $this->belongsToMany(\App\Models\NpcMenu::class, 'role_scope_permissions', 'role_id', 'menu_id')
            ->withPivot(['scope_id', 'permission_id'])
            ->wherePivot('scope_id', 'app_npc');
    }
}
