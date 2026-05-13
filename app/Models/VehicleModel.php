<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class VehicleModel extends Model
{
    use HasHashedId;

    use HasFactory;

    protected $table = 'models';

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
