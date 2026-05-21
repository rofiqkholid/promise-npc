<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcMasterCheckpoint extends Model
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
        'point_number',
        'category',
        'sequence_order',
        'check_item',
        'is_active'
    ];

    public function parts()
    {
        return $this->belongsToMany(NpcPart::class, 'npc_part_checkpoints', 'npc_master_checkpoint_id', 'npc_part_id');
    }
}
