<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\AlamatSpa;
use Illuminate\Http\Request;

class UsersAlamatController extends Controller
{
    function getAlamat()
    {
        $alamat = AlamatSpa::first();
        return response()->json([
            'success' => true,
            'message' => 'Alamat retrieved successfully',
            'data' => $alamat
        ]);
    }
}
