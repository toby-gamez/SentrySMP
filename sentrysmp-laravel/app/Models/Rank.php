<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    protected $table = 'ranks';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = ['Name', 'Description', 'Price', 'Sale', 'Image', 'GlobalMaxOrder'];

    public function getEffectivePriceAttribute(): float
    {
        return $this->Sale > 0 ? round($this->Price * (1 - $this->Sale / 100), 2) : $this->Price;
    }
}
