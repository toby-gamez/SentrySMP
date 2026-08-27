<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSettings extends Model
{
    protected $table = 'payment_settings';
    public $timestamps = false;

    protected $fillable = ['enable_payments', 'disable_stripe', 'disable_paypal', 'updated_at'];

    protected $casts = [
        'enable_payments' => 'boolean',
        'disable_stripe'  => 'boolean',
        'disable_paypal'  => 'boolean',
        'updated_at'      => 'datetime',
    ];

    public static function current(): self
    {
        try {
            return static::first() ?? new static([
                'enable_payments' => true,
                'disable_stripe'  => false,
                'disable_paypal'  => false,
            ]);
        } catch (\Throwable) {
            return new static(['enable_payments' => true, 'disable_stripe' => false, 'disable_paypal' => false]);
        }
    }
}
