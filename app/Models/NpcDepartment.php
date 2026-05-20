<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcDepartment extends Model
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

    protected $fillable = ['name', 'full_name', 'is_active'];

    public function processes()
    {
        return $this->belongsToMany(NpcProcess::class, 'npc_department_process', 'department_id', 'process_id');
    }
}
