<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Other extends Model
{
    protected $table = 'others';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = ['Name', 'Description', 'Price', 'ServerId', 'Sale', 'Image', 'GlobalMaxOrder'];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'ServerId', 'Id');
    }

    public function getEffectivePriceAttribute(): float
    {
        return $this->Sale > 0 ? round($this->Price * (1 - $this->Sale / 100), 2) : $this->Price;
    }
}
