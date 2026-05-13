<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcProductDetail extends Model
{
    use HasHashedId;

    protected $fillable = [
        'product_id',
        'sketch_image_path',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
