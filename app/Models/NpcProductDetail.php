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
        'process_type',
        'label_image_path',
        'master_checksheet_status',
        'checksheet_approved_by',
        'checksheet_approved_at',
        'reject_reason',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function checksheetApprover()
    {
        return $this->belongsTo(User::class, 'checksheet_approved_by');
    }
    
    public function isMasterChecksheetApproved()
    {
        return $this->master_checksheet_status === 'APPROVED';
    }
}
