<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'category_item');
    }

    public function categoryItems(): HasMany
    {
        return $this->hasMany(CategoryItem::class);
    }
}
