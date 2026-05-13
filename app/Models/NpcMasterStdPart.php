<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcMasterStdPart extends Model
{
    use HasHashedId;

    protected $fillable = [
        'name',
        'is_active'
    ];
}
