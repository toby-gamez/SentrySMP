<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherUsage extends Model
{
    protected $fillable = ['voucher_id', 'minecraft_username', 'used_at'];

    protected $casts = ['used_at' => 'datetime'];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
