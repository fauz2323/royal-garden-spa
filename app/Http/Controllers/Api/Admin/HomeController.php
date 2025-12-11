<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOrders;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    function index()
    {
        $user = User::where('role', 'customer')->count();
        $pendingService  = UserOrders::where('status', 'pending')->count();
        $completedService  = UserOrders::where('status', 'completed')->count();
        $dailyService  = UserOrders::whereDate('created_at', now()->toDateString())
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Admin dashboard data retrieved successfully',
            'data' => [
                'total_customers' => $user,
                'pending_services' => $pendingService,
                'completed_services' => $completedService,
                'daily_services' => $dailyService,
            ]
        ]);
    }
}
