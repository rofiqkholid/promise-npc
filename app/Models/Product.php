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
        return $this->hasOne(DocPackage::class, 'product_id')->where('is_active', true)->latest('id');
    }

    public function specChildParts()
    {
        return $this->hasMany(NpcSpecChildPart::class, 'product_id');
    }

    public function productDetail()
    {
        return $this->hasOne(NpcProductDetail::class, 'product_id');
    }
}
