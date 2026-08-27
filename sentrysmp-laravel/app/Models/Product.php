<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'price', 'sale', 'image', 'global_max_order', 'category_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(Command::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        return $this->sale > 0 ? round($this->price * (1 - $this->sale / 100), 2) : (float) $this->price;
    }
}
