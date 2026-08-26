<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSettings extends Model
{
    protected $table = 'paymentsettings';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = ['EnablePayments', 'DisableStripe', 'DisablePayPal', 'UpdatedAt'];

    protected $casts = [
        'EnablePayments' => 'boolean',
        'DisableStripe'  => 'boolean',
        'DisablePayPal'  => 'boolean',
        'UpdatedAt'      => 'datetime',
    ];

    public static function current(): self
    {
        try {
            return static::first() ?? new static([
                'EnablePayments' => true,
                'DisableStripe'  => false,
                'DisablePayPal'  => false,
            ]);
        } catch (\Throwable) {
            return new static(['EnablePayments' => true, 'DisableStripe' => false, 'DisablePayPal' => false]);
        }
    }
}
