<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserHistoryPoint;
use App\Models\UserOrders;
use App\Models\UserPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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

    function search(Request $request)
    {
        // Get the search keyword from the request (e.g., ?keyword=john)
        $keyword = $request->input('keyword');

        $users = User::query()
            // The 'when' method only executes the closure if $keyword is not empty
            ->when($keyword, function ($query, $keyword) {
                // Group the OR conditions inside a closure
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                        ->orWhere('email', 'LIKE', "%{$keyword}%")
                        ->orWhere('phone', 'LIKE', "%{$keyword}%");
                });
            })
            // Use paginate() instead of get() if you expect a lot of results
            ->paginate(200);

        // Return the results as JSON (or pass to a view)
        return response()->json($users);
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

    public function createUser(Request $request)
    {
        $defaultPassword = "user1234";

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($defaultPassword),
            'role' => 'customer', // Default role for new users
        ]);

        $point = UserPoint::create([
            'user_id' => $user->id,
            'points' => 0,
            'status' => 'active',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'created_at' => $user->created_at,

                ],
                'point' => [
                    'points' => $point->points,
                    'status' => $point->status,
                ],
                'token' => $token,
            ]
        ], 200);
    }

    function leaderboards()
    {

        $data = UserHistoryPoint::select('user_id', DB::raw('SUM(points) as total_points'))
            ->where('created_at', '>=', now()->startOfMonth())
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->take(3)
            ->with('user') // Optional: Eager load user details if the relationship is defined
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Leaderboard retrieved successfully',
            'data' => $data
        ], 200);
    }


    function historyorder(Request $request)
    {
        try {
            $orders = UserOrders::with(['spa_service'])
                ->where('user_id', $request->user_id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $orders
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    function historypoint(Request $request)
    {
        $history = UserHistoryPoint::where('user_id', $request->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'User points history retrieved successfully',
            'data' => $history
        ], 200);
    }

}
