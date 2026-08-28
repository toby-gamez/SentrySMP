<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
    protected $fillable = ['player', 'uuid', 'banner', 'reason', 'expires_at', 'banned_ago', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];
}
