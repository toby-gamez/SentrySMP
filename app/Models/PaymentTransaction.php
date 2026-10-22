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

    /**
     * Transactions where the money was actually received
     * (excludes PENDING / FAILED orders that never completed).
     */
    public function scopePaid($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'like', '%captured%')
              ->orWhere('status', 'like', '%succeeded%')
              ->orWhere('status', 'like', '%paid%');
        });
    }

    public function commandQueue()
    {
        return $this->hasMany(CommandQueue::class, 'transaction_id');
    }
}
