<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    //
    protected $guarded = [];

    /**
     * Get all of the userVoucher for the Voucher
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userVoucher()
    {
        return $this->hasMany(UserVoucher::class, 'voucher_id', 'id');
    }
}
