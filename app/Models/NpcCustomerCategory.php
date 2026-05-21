<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcCustomerCategory extends Model
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

    protected $fillable = ['customer_id', 'internal_category_id', 'name'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function internalCategory()
    {
        return $this->belongsTo(NpcInternalCategory::class, 'internal_category_id');
    }
}
