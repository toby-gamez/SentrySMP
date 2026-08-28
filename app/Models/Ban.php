<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
    protected $fillable = ['player', 'uuid', 'banner', 'reason', 'active', 'time', 'until'];

    protected $casts = [
        'active' => 'boolean',
        'time'   => 'integer',
        'until'  => 'integer',
    ];
}
