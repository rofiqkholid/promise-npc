<?php

namespace App\Models;




use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcMasterRouting extends Model
{

    // Activity log removed in favor of manual controller logging

    use HasHashedId;

    protected $fillable = ['part_id', 'process_id', 'department_id', 'sequence_order'];

    public function part()
    {
        return $this->belongsTo(Product::class, 'part_id');
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
