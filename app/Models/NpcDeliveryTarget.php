<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcDeliveryTarget extends Model
{
    use HasHashedId;

    use HasFactory;

    protected $fillable = [
        'target_name',
        'is_active'
    ];
}
