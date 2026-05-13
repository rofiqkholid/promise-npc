<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcInternalCategory extends Model
{
    use HasHashedId;

    protected $fillable = ['name'];

    public function customerCategories()
    {
        return $this->hasMany(NpcCustomerCategory::class, 'internal_category_id');
    }
}

