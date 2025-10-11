<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\SpaService;
use Illuminate\Http\Request;

class UserSpaServiceController extends Controller
{
    function getServicesList()
    {
        $services = SpaService::where('is_active', true)->get();
        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }
}
