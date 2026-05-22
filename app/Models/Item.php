<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'condition_id',
        'name',
        'brand_name',
        'description',
        'price',
        'is_sold',
    ];

    protected function casts(): array
    {
        return [
            'is_sold' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class);
    }

    public function itemImages(): HasMany
    {
        return $this->hasMany(ItemImage::class);
    }

    public function categoryItems(): HasMany
    {
        return $this->hasMany(CategoryItem::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_item');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function likedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function purchase(): HasOne
    {
        return $this->hasOne(Purchase::class);
    }
}
