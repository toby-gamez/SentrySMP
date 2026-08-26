<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    protected $table = 'teammembers';
    protected $primaryKey = 'Id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['Id', 'MinecraftName', 'TeamRankId', 'SkinUrl', 'SortOrder', 'TeamCategoryId'];

    public function category()
    {
        return $this->belongsTo(TeamCategory::class, 'TeamCategoryId', 'Id');
    }

    public function rank()
    {
        return $this->belongsTo(TeamRank::class, 'TeamRankId', 'Id');
    }
}
