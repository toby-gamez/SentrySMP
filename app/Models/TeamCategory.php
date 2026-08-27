<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamCategory extends Model
{
    protected $table = 'teamcategories';
    protected $primaryKey = 'Id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['Id', 'Name', 'SortOrder'];

    public function members()
    {
        return $this->hasMany(TeamMember::class, 'TeamCategoryId', 'Id')->orderBy('SortOrder');
    }
}
