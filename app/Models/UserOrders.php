<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOrders extends Model
{
    use HasFactory;

    protected $guarded = [];


    /**
     * Get the user that owns the UserOrders
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the service that owns the UserOrders
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function spa_service()
    {
        return $this->belongsTo(SpaService::class, 'spa_services_id', 'id');
    }
}
