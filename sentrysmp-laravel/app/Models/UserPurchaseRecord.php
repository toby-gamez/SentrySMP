<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPurchaseRecord extends Model
{
    protected $table = 'userpurchaserecords';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'MinecraftUsername', 'ProductType', 'ProductId',
        'TotalQuantityPurchased', 'LastPurchaseDate', 'CreatedAt',
    ];

    protected $casts = [
        'LastPurchaseDate' => 'datetime',
        'CreatedAt'        => 'datetime',
    ];
}
