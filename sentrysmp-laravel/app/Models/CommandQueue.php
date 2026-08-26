<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandQueue extends Model
{
    protected $table = 'command_queue';

    protected $fillable = ['transaction_id', 'player_name', 'command_text', 'status'];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'transaction_id');
    }
}
