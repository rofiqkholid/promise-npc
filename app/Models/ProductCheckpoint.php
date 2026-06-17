<?php

namespace App\Models;




use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class ProductCheckpoint extends Model
{

    // Activity log removed in favor of manual controller logging

    use HasHashedId;

    use HasFactory;

    protected $table = 'npc_product_checkpoints';

    protected $fillable = [
        'product_id',
        'npc_master_checkpoint_id',
        'custom_standard',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function masterCheckpoint()
    {
        return $this->belongsTo(NpcMasterCheckpoint::class, 'npc_master_checkpoint_id');
    }
}
