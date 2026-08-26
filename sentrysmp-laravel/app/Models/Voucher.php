<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $table = 'vouchers';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code', 'Description', 'StartDate', 'ExpirationDate',
        'MaxUses', 'CurrentUses', 'DiscountPercent',
        'Scope', 'ScopeCategory', 'ScopeItemId', 'IsActive',
    ];

    protected $casts = [
        'StartDate'      => 'datetime',
        'ExpirationDate' => 'datetime',
        'DiscountPercent'=> 'decimal:2',
        'IsActive'       => 'boolean',
    ];

    public function usages()
    {
        return $this->hasMany(VoucherUsage::class, 'VoucherId', 'Id');
    }
}
