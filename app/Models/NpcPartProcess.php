<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NpcPartProcess extends Model
{
    use HasHashedId;

    use HasFactory;

    protected $table = 'npc_part_processes';

    protected $fillable = [
        'npc_part_id',
        'process_id',
        'department_id',
        'target_completion_date',
        'actual_completion_date',
        'actual_qty',
        'photo_proof',
        'status',
        'sequence_order',
    ];

    public function part()
    {
        return $this->belongsTo(NpcPart::class, 'npc_part_id');
    }

    public function process()
    {
        return $this->belongsTo(NpcProcess::class, 'process_id');
    }

    public function department()
    {
        return $this->belongsTo(NpcDepartment::class, 'department_id');
    }
}
