<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVoucher extends Model
{
    protected $guarded = [];

    /**
     * Get the user that owns the UserVoucher
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the voucher that owns the UserVoucher
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id', 'id');
    }
}
