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
    /**
     * Display a listing of all orders.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = UserOrders::with(['user', 'spa_service']);

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date range if provided
            if ($request->has('date_from')) {
                $query->whereDate('date_service', '>=', $request->date_from);
            }
            if ($request->has('date_to')) {
                $query->whereDate('date_service', '<=', $request->date_to);
            }

            // Filter by user if provided
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

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

    /**
     * Store a newly created order in storage (Admin can create order for any user).
     */
    public function store(AdminCreateOrderRequest $request): JsonResponse
    {
        try {

            // Get the spa service to get the price
            $spaService = SpaService::findOrFail($request->spa_services_id);

            // Check if spa service is active
            if (!$spaService->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This spa service is currently not available'
                ], 400);
            }

            // Create the order
            $order = UserOrders::create([
                'user_id' => $request->user_id,
                'spa_services_id' => $request->spa_services_id,
                'price' => $spaService->price,
                'time_service' => $request->time_service,
                'date_service' => $request->date_service,
                'notes' => $request->notes,
                'status' => $request->get('status', 'pending')
            ]);

            // Load the relationships for response
            $order->load(['spa_service', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show($id): JsonResponse
    {
        try {
            $order = UserOrders::with(['spa_service', 'user'])->findOrFail($id);

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

    /**
     * Update the specified order in storage.
     */
    public function update(AdminUpdateOrderRequest $request, $id): JsonResponse
    {
        try {
            $order = UserOrders::findOrFail($id);

            // If spa service is being changed, update the price
            if ($request->has('spa_services_id')) {
                $spaService = SpaService::findOrFail($request->spa_services_id);
                if (!$spaService->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This spa service is currently not available'
                    ], 400);
                }
                $request->merge(['price' => $spaService->price]);
            }

            $order->update($request->only([
                'user_id',
                'spa_services_id',
                'price',
                'time_service',
                'date_service',
                'notes',
                'status'
            ]));

            $order->load(['spa_service', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified order from storage.
     */
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
    public function accept($id): JsonResponse
    {
        try {
            $order = UserOrders::findOrFail($id);

            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending orders can be accepted'
                ], 400);
            }

            $order->update(['status' => 'confirmed']);
            $order->load(['spa_service', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Order accepted successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject an order.
     */
    public function reject(Request $request, $id): JsonResponse
    {
        try {
            $order = UserOrders::findOrFail($id);

            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending orders can be rejected'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update notes with rejection reason if provided
            $notes = $order->notes;
            if ($request->has('rejection_reason')) {
                $notes = $notes ? $notes . "\n\nRejection reason: " . $request->rejection_reason
                    : "Rejection reason: " . $request->rejection_reason;
            }

            $order->update([
                'status' => 'rejected',
                'notes' => $notes
            ]);

            $order->load(['spa_service', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Order rejected successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order status to in progress.
     */
    public function startService($id): JsonResponse
    {
        try {
            $order = UserOrders::findOrFail($id);

            if ($order->status !== 'confirmed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only confirmed orders can be started'
                ], 400);
            }

            $order->update(['status' => 'in_progress']);
            $order->load(['spa_service', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Service started successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete an order.
     */
    public function complete($id): JsonResponse
    {
        try {
            $order = UserOrders::findOrFail($id);

            if (!in_array($order->status, ['confirmed', 'in_progress'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only confirmed or in-progress orders can be completed'
                ], 400);
            }

            $order->update(['status' => 'completed']);
            $order->load(['spa_service', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Order completed successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete order',
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
