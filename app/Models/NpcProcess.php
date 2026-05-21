<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcProcess extends Model
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

    use HasFactory;

    protected $fillable = [
        'process_name'
    ];

    public function departments()
    {
        return $this->belongsToMany(NpcDepartment::class, 'npc_department_process', 'process_id', 'department_id')->withTimestamps();
    }
}
