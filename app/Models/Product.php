<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class Product extends Model
{
    use HasHashedId;

    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'customer_id',
        'model_id',
        'part_no',
        'part_name',
        'is_active',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vehicleModel()
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }

    public function mappedCheckpoints()
    {
        return $this->hasMany(ProductCheckpoint::class, 'product_id');
    }

    public function historyProblems()
    {
        return $this->hasMany(ProductHistoryProblem::class, 'product_id')->orderBy('created_at', 'desc');
    }

    public function docPackage()
    {
        return $this->hasOne(DocPackage::class, 'product_id')
            ->where('is_active', true)
            ->where('is_delete', 0)
            ->whereNotNull('current_revision_id')
            ->latest('id');
    }

    public function siblings()
    {
        return $this->hasMany(Product::class, 'group_id', 'group_id')->where('id', '!=', $this->id);
    }

    public function getEffectiveDocPackage()
    {
        if ($this->docPackage) {
            return $this->docPackage;
        }

        if ($this->group_id && $this->relationLoaded('siblings')) {
            foreach ($this->siblings as $sibling) {
                if ($sibling->docPackage) {
                    return $sibling->docPackage;
                }
            }
        }
        
        // Fallback: Cari Product Master dengan part_no yang sama (versi yang lebih baru)
        // Ini berguna jika Master Data membuat ID Product baru untuk revisi ECN, 
        // namun transaksi produksi (NpcPart) masih menunjuk ke ID Product yang lama.
        if ($this->part_no) {
            $newerVersion = self::where('part_no', $this->part_no)
                ->where('id', '!=', $this->id)
                ->where('is_delete', 0)
                ->whereHas('docPackage')
                ->latest('id')
                ->first();
                
            if ($newerVersion && $newerVersion->docPackage) {
                return $newerVersion->docPackage;
            }
        }
        
        return null;
    }

    public function specChildParts()
    {
        return $this->hasMany(NpcSpecChildPart::class, 'product_id');
    }

    public function productDetail()
    {
        return $this->hasOne(NpcProductDetail::class, 'product_id');
    }

    public function getEffectiveProductDetail()
    {
        if ($this->productDetail && $this->productDetail->label_image_path) {
            return $this->productDetail;
        }

        // Fallback: Cari gambar dari versi yang lebih baru (is_delete = 0) dengan part_no yang sama
        if ($this->part_no) {
            $newerVersion = self::where('part_no', $this->part_no)
                ->where('id', '!=', $this->id)
                ->where('is_delete', 0)
                ->whereHas('productDetail', function ($q) {
                    $q->whereNotNull('label_image_path');
                })
                ->latest('id')
                ->first();
                
            if ($newerVersion && $newerVersion->productDetail) {
                return $newerVersion->productDetail;
            }
        }
        
        return $this->productDetail;
    }

    /**
     * Check if the master data for this product is completely configured.
     * Returns an array of missing components. If empty, the master data is complete.
     */
    public function getMissingMasterData()
    {
        $missing = [];
        $detail = $this->getEffectiveProductDetail();

        // 1. Label Image
        if (!$detail || empty($detail->label_image_path)) {
            $missing[] = 'Label Image';
        }

        // 2. Sketch/Image Checksheet
        if (!$detail || empty($detail->sketch_image_path)) {
            $missing[] = 'Image Checksheet (Sketch)';
        }

        // 3. Route Process
        $hasRouting = \App\Models\NpcMasterRouting::where('part_id', $this->id)->exists();
        if (!$hasRouting) {
            $missing[] = 'Route Process';
        }

        // 4. Point Checksheet
        if ($this->mappedCheckpoints()->count() === 0) {
            $missing[] = 'Point Checksheet (Check Points)';
        }

        // 5. Customer Mapping Master (Vehicle Model)
        if (empty($this->model_id)) {
            $missing[] = 'Customer Mapping Master (Vehicle Model)';
        }

        return $missing;
    }
}
