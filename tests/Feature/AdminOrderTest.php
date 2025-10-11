<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SpaService;
use App\Models\UserOrders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $customer;
    protected $spaService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create([
            'role' => 'admin'
        ]);

        // Create a customer user
        $this->customer = User::factory()->create([
            'role' => 'customer'
        ]);

        // Create an active spa service
        $this->spaService = SpaService::create([
            'name' => 'Test Spa Service',
            'description' => 'Test description',
            'price' => 100.00,
            'duration' => 60,
            'category' => 'massage',
            'is_active' => true
        ]);
    }

    public function test_admin_can_get_all_orders()
    {
        Sanctum::actingAs($this->admin);

        // Create some orders
        UserOrders::factory()->count(5)->create([
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price
        ]);

        $response = $this->getJson('/api/admin/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'spa_services_id',
                        'status',
                        'price',
                        'time_service',
                        'date_service',
                        'user',
                        'spa_service'
                    ]
                ],
                'pagination'
            ]);
    }

    public function test_admin_can_filter_orders_by_status()
    {
        Sanctum::actingAs($this->admin);

        // Create orders with different statuses
        UserOrders::create([
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending'
        ]);

        UserOrders::create([
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '15:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'confirmed'
        ]);

        $response = $this->getJson('/api/admin/orders?status=pending');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('pending', $data[0]['status']);
    }

    public function test_admin_can_create_order_for_customer()
    {
        Sanctum::actingAs($this->admin);

        $orderData = [
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'notes' => 'Admin created order',
            'status' => 'confirmed'
        ];

        $response = $this->postJson('/api/admin/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'spa_services_id',
                    'status',
                    'price',
                    'time_service',
                    'date_service',
                    'notes',
                    'spa_service',
                    'user'
                ]
            ]);

        $this->assertDatabaseHas('user_orders', [
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'status' => 'confirmed',
            'price' => $this->spaService->price,
            'notes' => 'Admin created order'
        ]);
    }

    public function test_admin_can_view_specific_order()
    {
        Sanctum::actingAs($this->admin);

        $order = UserOrders::create([
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending'
        ]);

        $response = $this->getJson("/api/admin/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'spa_services_id',
                    'status',
                    'price',
                    'time_service',
                    'date_service',
                    'spa_service',
                    'user'
                ]
            ]);
    }

    public function test_admin_can_update_order()
    {
        Sanctum::actingAs($this->admin);

        $order = UserOrders::create([
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending'
        ]);

        $updateData = [
            'time_service' => '15:30',
            'status' => 'confirmed',
            'notes' => 'Updated by admin'
        ];

        $response = $this->putJson("/api/admin/orders/{$order->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_orders', [
            'id' => $order->id,
            'time_service' => '15:30',
            'status' => 'confirmed',
            'notes' => 'Updated by admin'
        ]);
    }

    public function test_admin_can_accept_pending_order()
    {
        Sanctum::actingAs($this->admin);

        $order = UserOrders::create([
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending'
        ]);

        $response = $this->postJson("/api/admin/orders/{$order->id}/accept");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order accepted successfully'
            ]);

        $this->assertDatabaseHas('user_orders', [
            'id' => $order->id,
            'status' => 'confirmed'
        ]);
    }

    public function test_admin_cannot_accept_non_pending_order()
    {
        Sanctum::actingAs($this->admin);

        $order = UserOrders::create([
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'confirmed'
        ]);

        $response = $this->postJson("/api/admin/orders/{$order->id}/accept");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Only pending orders can be accepted'
            ]);
    }

    public function test_admin_can_reject_pending_order()
    {
        Sanctum::actingAs($this->admin);

        $order = UserOrders::create([
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending'
        ]);

        $rejectionData = [
            'rejection_reason' => 'Service unavailable on that date'
        ];

        $response = $this->postJson("/api/admin/orders/{$order->id}/reject", $rejectionData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order rejected successfully'
            ]);

        $this->assertDatabaseHas('user_orders', [
            'id' => $order->id,
            'status' => 'rejected'
        ]);

        // Check if rejection reason was added to notes
        $updatedOrder = UserOrders::find($order->id);
        $this->assertStringContainsString('Service unavailable on that date', $updatedOrder->notes);
    }

    public function test_admin_can_start_confirmed_service()
    {
        Sanctum::actingAs($this->admin);

        $order = UserOrders::create([
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'confirmed'
        ]);

        $response = $this->postJson("/api/admin/orders/{$order->id}/start");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Service started successfully'
            ]);

        $this->assertDatabaseHas('user_orders', [
            'id' => $order->id,
            'status' => 'in_progress'
        ]);
    }

    public function test_admin_can_complete_order()
    {
        Sanctum::actingAs($this->admin);

        $order = UserOrders::create([
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'in_progress'
        ]);

        $response = $this->postJson("/api/admin/orders/{$order->id}/complete");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order completed successfully'
            ]);

        $this->assertDatabaseHas('user_orders', [
            'id' => $order->id,
            'status' => 'completed'
        ]);
    }

    public function test_admin_can_delete_order()
    {
        Sanctum::actingAs($this->admin);

        $order = UserOrders::create([
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending'
        ]);

        $response = $this->deleteJson("/api/admin/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);

        $this->assertDatabaseMissing('user_orders', [
            'id' => $order->id
        ]);
    }

    public function test_admin_can_get_statistics()
    {
        Sanctum::actingAs($this->admin);

        // Create orders with different statuses
        UserOrders::create([
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'price' => 100.00,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending'
        ]);

        UserOrders::create([
            'user_id' => $this->customer->id,
            'spa_services_id' => $this->spaService->id,
            'price' => 150.00,
            'time_service' => '15:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'completed'
        ]);

        $response = $this->getJson('/api/admin/orders-statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_orders',
                    'pending_orders',
                    'confirmed_orders',
                    'in_progress_orders',
                    'completed_orders',
                    'cancelled_orders',
                    'rejected_orders',
                    'today_orders',
                    'this_week_orders',
                    'this_month_orders',
                    'total_revenue',
                    'this_month_revenue'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals(2, $data['total_orders']);
        $this->assertEquals(1, $data['pending_orders']);
        $this->assertEquals(1, $data['completed_orders']);
        $this->assertEquals(150.00, $data['total_revenue']);
    }

    public function test_customer_cannot_access_admin_orders()
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/admin/orders');

        $response->assertStatus(403);
    }

    public function test_admin_cannot_create_order_with_invalid_data()
    {
        Sanctum::actingAs($this->admin);

        $orderData = [
            'user_id' => 999, // Non-existent user
            'spa_services_id' => 999, // Non-existent service
            'time_service' => 'invalid-time',
            'date_service' => '2020-01-01', // Past date
        ];

        $response = $this->postJson('/api/admin/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'spa_services_id', 'time_service', 'date_service']);
    }
}
