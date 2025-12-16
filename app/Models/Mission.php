<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    //
    protected $guarded = [];

    /**
     * Get all of the userMission for the Mission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userMission()
    {
        return $this->hasMany(UserMission::class, 'mission_id', 'id');
    }
}
