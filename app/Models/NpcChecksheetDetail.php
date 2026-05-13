<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcChecksheetDetail extends Model
{
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
