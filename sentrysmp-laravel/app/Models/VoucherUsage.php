<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherUsage extends Model
{
    protected $table = 'voucherusages';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = ['VoucherId', 'MinecraftUsername', 'UsedAt'];

    protected $casts = ['UsedAt' => 'datetime'];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'VoucherId', 'Id');
    }
}
