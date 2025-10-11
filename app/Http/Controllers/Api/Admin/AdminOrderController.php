<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCreateOrderRequest;
use App\Http\Requests\AdminUpdateOrderRequest;
use App\Models\UserOrders;
use App\Models\SpaService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdminOrderController extends Controller
{

    public function index(): JsonResponse
    {
        try {
            $query = UserOrders::with(['user', 'spa_service']);

            $query->where('status', 'pending');

            $orders = $query->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'total_pages' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request): JsonResponse
    {
        try {
            $order = UserOrders::with(['spa_service', 'user'])->findOrFail($request->id);

            return response()->json([
                'success' => true,
                'message' => 'Order retrieved successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }


    public function destroy($id): JsonResponse
    {
        try {
            $order = UserOrders::findOrFail($id);
            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept/Confirm an order.
     */
    public function changeStatus(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:user_orders,id',
            'status' => 'required|string|in:confirmed,in_progress,completed,cancelled,rejected'
        ]);

        try {
            $order = UserOrders::findOrFail($request->id);

            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending orders can be accepted'
                ], 400);
            }

            $order->update(['status' => $request->status]);
            $order->load(['spa_service', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders statistics for dashboard.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_orders' => UserOrders::count(),
                'pending_orders' => UserOrders::where('status', 'pending')->count(),
                'confirmed_orders' => UserOrders::where('status', 'confirmed')->count(),
                'in_progress_orders' => UserOrders::where('status', 'in_progress')->count(),
                'completed_orders' => UserOrders::where('status', 'completed')->count(),
                'cancelled_orders' => UserOrders::where('status', 'cancelled')->count(),
                'rejected_orders' => UserOrders::where('status', 'rejected')->count(),
                'today_orders' => UserOrders::whereDate('created_at', today())->count(),
                'this_week_orders' => UserOrders::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'this_month_orders' => UserOrders::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'total_revenue' => UserOrders::where('status', 'completed')->sum('price'),
                'this_month_revenue' => UserOrders::where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('price')
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
