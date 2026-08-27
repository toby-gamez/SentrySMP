<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $table = 'payment_transactions';
    public $timestamps = false;

    protected $fillable = [
        'provider', 'provider_transaction_id', 'amount', 'currency',
        'minecraft_username', 'items_json', 'status', 'raw_response', 'created_at',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function commandQueue()
    {
        return $this->hasMany(CommandQueue::class, 'transaction_id');
    }
}
