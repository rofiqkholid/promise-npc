<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class DocPackageRevision extends Model
{
    use HasHashedId;

    protected $table = 'doc_package_revisions';

    public function package()
    {
        return $this->belongsTo(DocPackage::class, 'package_id');
    }
}
