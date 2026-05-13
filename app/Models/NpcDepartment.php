<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcDepartment extends Model
{
    use HasHashedId;

    protected $fillable = ['name', 'full_name', 'is_active'];

    public function processes()
    {
        return $this->belongsToMany(NpcProcess::class, 'npc_department_process', 'department_id', 'process_id');
    }
}
