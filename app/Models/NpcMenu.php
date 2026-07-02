<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class NpcMenu extends Model
{
    use HasHashedId;

    protected $fillable = [
        'scope_id',
        'parent_id',
        'title',
        'route',
        'icon',
        'sort_order',
        'level',
        'is_active',
        'is_visible',
    ];

    public function parent()
    {
        return $this->belongsTo(NpcMenu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(NpcMenu::class, 'parent_id')->orderBy('sort_order');
    }
}
