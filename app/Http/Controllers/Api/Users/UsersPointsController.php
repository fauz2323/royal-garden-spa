<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\UserHistoryPoint;
use App\Models\UserPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersPointsController extends Controller
{
    function index()
    {
        $userPoints = UserPoint::where('user_id', Auth::user()->id)->first();

        return response()->json([
            'success' => true,
            'message' => 'User points retrieved successfully',
            'data' => $userPoints
        ], 200);
    }


    function getHistory()
    {
        $history = UserHistoryPoint::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'User points history retrieved successfully',
            'data' => $history
        ], 200);
    }
}
