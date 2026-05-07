<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpcPart extends Model
{
    use HasFactory;

    protected $table = 'npc_parts';

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($part) {
            $part->processes()->delete();
            if ($part->checksheet) {
                $part->checksheet->details()->delete();
                $part->checksheet->delete();
            }
        });
    }

    protected $fillable = [
        'npc_event_id',
        'product_id',
        'qty',
        'delivery_date',
        'actual_delivery',
        'actual_completion_date',
        'production_notes',
        'status',
        'qc_target_date',
        'mgm_target_date',
        'condition',
        'delivered_qty',
        'part_revision_id'
    ];

    public function event()
    {
        return $this->belongsTo(NpcEvent::class, 'npc_event_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Custom accessor / relationship-like method to get event if heavily relied upon
    public function getEventAttribute()
    {
        // This is kept for backwards compatibility if needed, but the true relationship is now event()
        return $this->event()->first();
    }

    public function checksheet()
    {
        return $this->hasOne(NpcChecksheet::class, 'npc_part_id');
    }

    public function processes()
    {
        return $this->hasMany(NpcPartProcess::class, 'npc_part_id')->orderBy('sequence_order');
    }

    public function checkpoints()
    {
        return $this->belongsToMany(NpcMasterCheckpoint::class, 'npc_part_checkpoints', 'npc_part_id', 'npc_master_checkpoint_id');
    }

    public function drawingRevision()
    {
        return $this->belongsTo(DocPackageRevision::class, 'part_revision_id');
    }

    public function getHasEcnUpdateAttribute()
    {
        // If it doesn't have a product or doc package, we can't check
        if (!$this->product || !$this->product->docPackage) {
            return false;
        }
        
        // If part_revision_id is null, maybe it was created before this feature
        if (!$this->part_revision_id) {
            return false;
        }

        // True if the current_revision_id in doc_packages differs from what we saved in part_revision_id
        return $this->part_revision_id !== $this->product->docPackage->current_revision_id;
    }
}
