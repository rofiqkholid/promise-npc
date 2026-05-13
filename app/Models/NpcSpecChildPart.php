<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcSpecChildPart extends Model
{
    use HasHashedId;

    protected $fillable = [
        'product_id',
        'part_type',
        'sequence_label',
        'inventory_material_id',
        'std_part_id',
        'thickness',
        'spec'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function stdPart()
    {
        return $this->belongsTo(NpcMasterStdPart::class, 'std_part_id');
    }
}
