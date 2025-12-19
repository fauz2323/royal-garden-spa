<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserOrderRequest;
use App\Http\Requests\UpdateUserOrderRequest;
use App\Models\UserOrders;
use App\Models\SpaService;
use App\Models\UserHistoryPoint;
use App\Models\UserPoint;
use App\Services\firebaseServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserOrderController extends Controller
{
    /**
     * Display a listing of user's orders.
     */
    public function index(): JsonResponse
    {
        try {
            $orders = UserOrders::with(['spa_service'])
                ->where('user_id', Auth::id())
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

    /**
     * Store a newly created order in storage.
     */
    public function store(StoreUserOrderRequest $request): JsonResponse
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
                'user_id' => Auth::id(),
                'spa_services_id' => $request->spa_services_id,
                'price' => $spaService->price + rand(100, 999),
                'time_service' => $request->time_service,
                'date_service' => $request->date_service,
                'notes' => $request->notes ?? '-',
                'status' => 'pending'
            ]);

            // Load the relationships for response
            $order->load(['spa_service', 'user']);

            $firebaseService = new firebaseServices();
            $firebaseService->sendToTopic(
                'admin_notifications',
                'New Order Created',
                'A new order has been created by ' . Auth::user()->name,
                ['order_id' => (string)$order->id]
            );

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
    public function show(Request $request): JsonResponse
    {
        try {
            $order = UserOrders::with(['spa_service', 'user'])
                ->where('user_id', Auth::id())
                ->findOrFail($request->id);

            return response()->json([
                'success' => true,
                'message' => 'Order retrieved successfullyxxx',
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
     * Cancel the specified order.
     * Only allow cancellation if order is still pending
     */
    public function cancel(Request $request): JsonResponse
    {
        try {
            $order = UserOrders::where('user_id', Auth::id())->findOrFail($request->id);

            // Only allow cancellation if order is still pending
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel order that is no longer pending'
                ], 400);
            }

            $order->update(['status' => 'cancelled']);
            $order->load(['spa_service', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
