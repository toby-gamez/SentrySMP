<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamRank extends Model
{
    protected $table = 'teamranks';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = ['Name', 'HexColor'];

    public function members()
    {
        return $this->hasMany(TeamMember::class, 'TeamRankId', 'Id');
    }
}
