<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserHistoryPoint;
use App\Models\UserPoint;
use Illuminate\Http\Request;

class AdminUserPointController extends Controller
{
    function index()
    {
        $points = UserPoint::with('user')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $points
        ]);
    }

    function addPoints(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer',
            'description' => 'nullable|string'
        ]);

        $userPoint = UserPoint::where('user_id', $request->user_id)
            ->first();

        $userPoint->points += $request->points;
        $userPoint->save();

        $pointHistory = new UserHistoryPoint();
        $pointHistory->user_id = $request->user_id;
        $pointHistory->points = $request->points;
        $pointHistory->description = $request->description ?? 'Admin added points';
        $pointHistory->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Points added successfully',
            'data' => $userPoint
        ]);
    }
}
