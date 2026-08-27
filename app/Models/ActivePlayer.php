<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivePlayer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'username', 'coins', 'money', 'rank',
        'play_time_seconds', 'play_time_ticks',
        'deaths', 'player_kills', 'mobs_killed', 'blocks_travelled',
        'error', 'reported_at',
    ];

    protected $casts = ['reported_at' => 'datetime'];
}
