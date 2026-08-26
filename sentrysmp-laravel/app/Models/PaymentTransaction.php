<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $table = 'paymenttransactions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Provider', 'ProviderTransactionId', 'Amount', 'Currency',
        'MinecraftUsername', 'ItemsJson', 'Status', 'RawResponse', 'CreatedAt',
    ];

    protected $casts = [
        'Amount'    => 'decimal:2',
        'CreatedAt' => 'datetime',
    ];

    public function commandQueue()
    {
        return $this->hasMany(CommandQueue::class, 'transaction_id', 'Id');
    }
}
