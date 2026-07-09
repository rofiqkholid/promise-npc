<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcEvent extends Model
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

    protected $table = 'npc_events';

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($event) {
            \Spatie\Activitylog\Facades\Activity::withoutLogs(function () use ($event) {
                foreach ($event->parts as $part) {
                    $part->delete();
                }
            });
        });
    }

    protected $fillable = [
        'po_no',
        'delivery_to',
        'customer_category_id',
        'delivery_group_id',
        'model_id'
    ];


    public function parts()
    {
        return $this->hasMany(NpcPart::class, 'npc_event_id');
    }

    public function customerCategory()
    {
        return $this->belongsTo(NpcCustomerCategory::class, 'customer_category_id');
    }

    public function deliveryGroup()
    {
        return $this->belongsTo(NpcDeliveryGroup::class, 'delivery_group_id');
    }

    public function vehicleModel()
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }
}
