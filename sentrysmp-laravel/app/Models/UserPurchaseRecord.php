<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPurchaseRecord extends Model
{
    protected $table = 'user_purchase_records';
    public $timestamps = false;

    protected $fillable = [
        'minecraft_username', 'product_id',
        'total_quantity_purchased', 'last_purchase_date', 'created_at',
    ];

    protected $casts = [
        'last_purchase_date' => 'datetime',
        'created_at'         => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
