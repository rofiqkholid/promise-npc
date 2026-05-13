<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class DocPackage extends Model
{
    use HasHashedId;

    protected $table = 'doc_packages';
    
    // As per user, it takes revision and ecn from doc_package_revisions
    // using current_revision_id
    public function currentRevision()
    {
        return $this->belongsTo(DocPackageRevision::class, 'current_revision_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
