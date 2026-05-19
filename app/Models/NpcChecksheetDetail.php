<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;


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
            ->dontLogEmptyChanges();
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
