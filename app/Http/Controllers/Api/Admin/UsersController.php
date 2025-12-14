<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPoint;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    function index()
    {
        $users = User::where('role', 'customer')->select('id', 'name', 'email', 'role', 'phone', 'created_at')->with('point')->orderBy('created_at', 'desc')->get()->makeHidden('id');

        return response()->json([
            'success' => true,
            'message' => 'Customer users retrieved successfully',
            'data' => [
                'users' => $users
            ]
        ]);
    }

    function detail(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'role', 'phone', 'created_at')->with('point')->where('email', $request->email)->first();

        return response()->json([
            'success' => true,
            'message' => 'Customer users retrieved successfully',
            'data' => $users
        ]);
    }

    function points()
    {
        $data = UserPoint::with('user')
            ->orderBy('points', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Leaderboard retrieved successfully',
            'data' => $data
        ], 200);
    }
}
