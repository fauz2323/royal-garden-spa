<?php

namespace Tests\Feature;

use App\Models\SpaService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSpaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->customer = User::factory()->create();
    }

    public function test_admin_can_view_all_spa_services()
    {
        SpaService::create([
            'name' => 'Test Service',
            'description' => 'Test Description',
            'price' => 100000,
            'duration' => 60,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/spa-services');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'services' => [
                        '*' => ['id', 'name', 'description', 'price', 'duration', 'is_active']
                    ]
                ]
            ]);
    }

    public function test_admin_can_create_spa_service()
    {
        $serviceData = [
            'name' => 'New Spa Service',
            'description' => 'New spa service description',
            'price' => 150000,
            'duration' => 90,
            'is_active' => true
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/spa-services', $serviceData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Spa service created successfully'
            ]);

        $this->assertDatabaseHas('spa_services', [
            'name' => 'New Spa Service',
            'price' => 150000
        ]);
    }

    public function test_admin_can_view_single_spa_service()
    {
        $service = SpaService::create([
            'name' => 'Test Service',
            'description' => 'Test Description',
            'price' => 100000,
            'duration' => 60,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/admin/spa-services/{$service->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'service' => [
                        'id' => $service->id,
                        'name' => 'Test Service'
                    ]
                ]
            ]);
    }

    public function test_admin_can_update_spa_service()
    {
        $service = SpaService::create([
            'name' => 'Test Service',
            'description' => 'Test Description',
            'price' => 100000,
            'duration' => 60,
            'is_active' => true
        ]);

        $updateData = [
            'name' => 'Updated Service',
            'price' => 200000
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/admin/spa-services/{$service->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Spa service updated successfully'
            ]);

        $this->assertDatabaseHas('spa_services', [
            'id' => $service->id,
            'name' => 'Updated Service',
            'price' => 200000
        ]);
    }

    public function test_admin_can_delete_spa_service()
    {
        $service = SpaService::create([
            'name' => 'Test Service',
            'description' => 'Test Description',
            'price' => 100000,
            'duration' => 60,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/admin/spa-services/{$service->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Spa service deleted successfully'
            ]);

        $this->assertDatabaseMissing('spa_services', [
            'id' => $service->id
        ]);
    }

    public function test_admin_can_toggle_service_status()
    {
        $service = SpaService::create([
            'name' => 'Test Service',
            'description' => 'Test Description',
            'price' => 100000,
            'duration' => 60,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/admin/spa-services/{$service->id}/toggle-status");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Service status updated successfully'
            ]);

        $this->assertDatabaseHas('spa_services', [
            'id' => $service->id,
            'is_active' => false
        ]);
    }

    public function test_customer_cannot_access_admin_routes()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson('/api/admin/spa-services');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_routes()
    {
        $response = $this->getJson('/api/admin/spa-services');

        $response->assertStatus(401);
    }

    public function test_validation_errors_on_create()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/spa-services', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description', 'price', 'duration']);
    }

    public function test_404_on_non_existent_service()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/spa-services/999');

        $response->assertStatus(404);
    }
}
