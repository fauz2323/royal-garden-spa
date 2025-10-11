<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SpaService;
use App\Models\UserOrders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class UserOrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $spaService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user
        $this->user = User::factory()->create([
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

    public function test_user_can_get_available_services()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/customer/services');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'uuid',
                        'name',
                        'description',
                        'price',
                        'duration',
                        'category'
                    ]
                ]
            ]);
    }

    public function test_user_can_create_order()
    {
        Sanctum::actingAs($this->user);

        $orderData = [
            'spa_services_id' => $this->spaService->id,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'notes' => 'Test notes'
        ];

        $response = $this->postJson('/api/customer/orders', $orderData);

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
            'user_id' => $this->user->id,
            'spa_services_id' => $this->spaService->id,
            'status' => 'pending',
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'notes' => 'Test notes'
        ]);
    }

    public function test_user_cannot_create_order_with_invalid_data()
    {
        Sanctum::actingAs($this->user);

        $orderData = [
            'spa_services_id' => 999, // Non-existent service
            'time_service' => 'invalid-time',
            'date_service' => '2020-01-01', // Past date
        ];

        $response = $this->postJson('/api/customer/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['spa_services_id', 'time_service', 'date_service']);
    }

    public function test_user_can_get_their_orders()
    {
        Sanctum::actingAs($this->user);

        // Create an order
        $order = UserOrders::create([
            'user_id' => $this->user->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending'
        ]);

        $response = $this->getJson('/api/customer/orders');

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
                        'spa_service'
                    ]
                ]
            ]);
    }

    public function test_user_can_view_specific_order()
    {
        Sanctum::actingAs($this->user);

        $order = UserOrders::create([
            'user_id' => $this->user->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending'
        ]);

        $response = $this->getJson("/api/customer/orders/{$order->id}");

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

    public function test_user_can_update_pending_order()
    {
        Sanctum::actingAs($this->user);

        $order = UserOrders::create([
            'user_id' => $this->user->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending'
        ]);

        $updateData = [
            'time_service' => '15:30',
            'notes' => 'Updated notes'
        ];

        $response = $this->putJson("/api/customer/orders/{$order->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_orders', [
            'id' => $order->id,
            'time_service' => '15:30',
            'notes' => 'Updated notes'
        ]);
    }

    public function test_user_cannot_update_non_pending_order()
    {
        Sanctum::actingAs($this->user);

        $order = UserOrders::create([
            'user_id' => $this->user->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'confirmed'
        ]);

        $updateData = [
            'time_service' => '15:30'
        ];

        $response = $this->putJson("/api/customer/orders/{$order->id}", $updateData);

        $response->assertStatus(400);
    }

    public function test_user_can_cancel_pending_order()
    {
        Sanctum::actingAs($this->user);

        $order = UserOrders::create([
            'user_id' => $this->user->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending'
        ]);

        $response = $this->postJson("/api/customer/orders/{$order->id}/cancel");

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_orders', [
            'id' => $order->id,
            'status' => 'cancelled'
        ]);
    }

    public function test_user_cannot_access_other_users_orders()
    {
        $otherUser = User::factory()->create(['role' => 'customer']);

        $order = UserOrders::create([
            'user_id' => $otherUser->id,
            'spa_services_id' => $this->spaService->id,
            'price' => $this->spaService->price,
            'time_service' => '14:30',
            'date_service' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending'
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/customer/orders/{$order->id}");

        $response->assertStatus(404);
    }
}
