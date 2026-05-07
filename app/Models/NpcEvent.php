<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpcEvent extends Model
{
    use HasFactory;

    protected $table = 'npc_events';

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($event) {
            foreach ($event->parts as $part) {
                $part->delete();
            }
        });
    }

    protected $fillable = [
        'po_no',
        'delivery_to',
        'customer_category_id',
        'delivery_group_id'
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
}
