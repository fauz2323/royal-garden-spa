<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\UsersOrdersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCreateOrderRequest;
use App\Http\Requests\AdminUpdateOrderRequest;
use App\Models\UserOrders;
use App\Models\SpaService;
use App\Models\User;
use App\Models\UserHistoryPoint;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Excel;

class AdminOrderController extends Controller
{

    public function index($status): JsonResponse
    {
        try {
            if ($status == 'pending') {
                $statuses = ['pending'];
            } else {
                $statuses = ['confirmed', 'in_progress', 'completed', 'cancelled', 'rejected', 'pending'];
            }
            $query = UserOrders::with(['user', 'spa_service']);

            $query->whereIn('status', $statuses);

            $orders = $query->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Orders retrieved successfullssy',
                'data' => $orders,
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
                'message' => 'Order retrieved successfullyss',
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
            'status' => 'required|string|in:confirmed,in_progress,completed,rejected'
        ]);

        try {
            $order = UserOrders::findOrFail($request->id);

            if ($order->status === 'completed' || $order->status === 'cancelled' || $order->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot change status of completed, cancelled, or rejected orders'
                ], 400);
            }

            $order->update(['status' => $request->status]);
            $order->load(['spa_service', 'user']);

            if ($request->status == 'completed') {
                $user = User::find($order->user_id);
                if ($user) {
                    $user->point->points += $order->spa_service->points;
                    $user->point->save();

                    $userHistoryPoints = new UserHistoryPoint();
                    $userHistoryPoints->user_id = $user->id;
                    $userHistoryPoints->points = $order->spa_service->points;
                    $userHistoryPoints->description = 'Points earned from completing order #' . $order->id;
                    $userHistoryPoints->save();
                }
            }

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

    function getExcelOrder()
    {
        return Excel::download(new UsersOrdersExport(), 'orders.xlsx');
    }
}
