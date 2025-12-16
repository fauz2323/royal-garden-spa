<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMission extends Model
{
    //
    protected $guarded = [];

    /**
     * Get the mission that owns the UserMission
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mission()
    {
        return $this->belongsTo(Mission::class, 'mission_id', 'id');
    }

    /**
     * Get the user that owns the UserMission
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
