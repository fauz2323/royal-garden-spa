<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Http\Request;

class MissionAdminController extends Controller
{
    function index()
    {
        $missions = Mission::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Missions retrieved successfully',
            'data' => $missions
        ]);
    }
}
