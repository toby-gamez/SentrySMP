<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Command extends Model
{
    protected $table = 'commands';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = ['CommandText', 'Type', 'TypeId'];
}
