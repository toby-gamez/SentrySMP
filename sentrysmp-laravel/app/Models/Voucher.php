<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = [
        'code', 'description', 'start_date', 'expiration_date',
        'max_uses', 'current_uses', 'discount_percent',
        'scope', 'scope_category', 'scope_item_id', 'is_active',
    ];

    protected $casts = [
        'start_date'      => 'datetime',
        'expiration_date' => 'datetime',
        'discount_percent'=> 'decimal:2',
        'is_active'       => 'boolean',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }
}
