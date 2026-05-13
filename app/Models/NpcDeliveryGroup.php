<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcDeliveryGroup extends Model
{
    use HasHashedId;

    protected $fillable = ['name'];
}
