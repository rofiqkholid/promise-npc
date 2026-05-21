<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcChecksheetDetail extends Model
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

    protected $fillable = [
        'npc_checksheet_id',
        'point_check',
        'standard',
        'samples',
        'row_result'
    ];

    protected $casts = [
        'samples' => 'array',
    ];

    public function checksheet()
    {
        return $this->belongsTo(NpcChecksheet::class, 'npc_checksheet_id');
    }
}
