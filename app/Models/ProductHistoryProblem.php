<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class ProductHistoryProblem extends Model
{
    use HasHashedId;

    use HasFactory;

    protected $table = 'product_history_problems';

    protected $fillable = [
        'product_id',
        'problem_description',
        'npc_part_id_finder',
        'created_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function partFinder()
    {
        return $this->belongsTo(NpcPart::class, 'npc_part_id_finder');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
