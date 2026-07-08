<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcChecksheet extends Model
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
        'npc_part_id',
        'qe_checked_by',
        'qe_check_date',
        'accuracy_percentage',
        'attachment_path',
        'mgm_checked_by',
        'mgm_check_date',
        'final_result',
        'qe_staff_id', 'qe_staff_date',
        'qe_spv_id', 'qe_spv_date',
        'qe_assman_id', 'qe_assman_date',
        'qe_mgr_id', 'qe_mgr_date',
        'mgm_staff_id', 'mgm_staff_date',
        'mgm_spv_id', 'mgm_spv_date',
        'mgm_assman_id', 'mgm_assman_date',
        'mgm_mgr_id', 'mgm_mgr_date',
        'approval_status',
    ];

    public function npcPart()
    {
        return $this->belongsTo(NpcPart::class);
    }

    public function details()
    {
        return $this->hasMany(NpcChecksheetDetail::class, 'npc_checksheet_id');
    }

    public function qeChecker()
    {
        return $this->belongsTo(User::class, 'qe_checked_by', 'id');
    }

    public function mgmChecker()
    {
        return $this->belongsTo(User::class, 'mgm_checked_by', 'id');
    }

    public function qeStaff()
    {
        return $this->belongsTo(User::class, 'qe_staff_id', 'id');
    }

    public function qeSpv()
    {
        return $this->belongsTo(User::class, 'qe_spv_id', 'id');
    }

    public function qeMgr()
    {
        return $this->belongsTo(User::class, 'qe_mgr_id', 'id');
    }

    public function mgmStaff()
    {
        return $this->belongsTo(User::class, 'mgm_staff_id', 'id');
    }

    public function mgmSpv()
    {
        return $this->belongsTo(User::class, 'mgm_spv_id', 'id');
    }

    public function mgmMgr()
    {
        return $this->belongsTo(User::class, 'mgm_mgr_id', 'id');
    }

    public function qeAssman()
    {
        return $this->belongsTo(User::class, 'qe_assman_id', 'id');
    }

    public function mgmAssman()
    {
        return $this->belongsTo(User::class, 'mgm_assman_id', 'id');
    }
}
