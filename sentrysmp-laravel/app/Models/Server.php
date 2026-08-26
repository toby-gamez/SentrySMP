<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $table = 'servers';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = ['Name', 'RCONIP', 'RCONPort', 'RCONPassword'];

    public function keys() { return $this->hasMany(Key::class, 'ServerId', 'Id'); }
    public function coins() { return $this->hasMany(Coin::class, 'ServerId', 'Id'); }
    public function bundles() { return $this->hasMany(Bundle::class, 'ServerId', 'Id'); }
    public function battlepasses() { return $this->hasMany(BattlePass::class, 'ServerId', 'Id'); }
    public function others() { return $this->hasMany(Other::class, 'ServerId', 'Id'); }
}
