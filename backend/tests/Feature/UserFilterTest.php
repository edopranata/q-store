<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Role;
use Tests\TestCase;

class UserFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'user', 'guard_name' => 'web']);
        Role::create(['name' => 'manager', 'guard_name' => 'web']);
        
        // Create test users
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'is_active' => true
        ]);
        $adminUser->assignRole('admin');
        
        $regularUser = User::factory()->create([
            'name' => 'Regular User', 
            'email' => 'user@test.com',
            'is_active' => true
        ]);
        $regularUser->assignRole('user');
        
        $managerUser = User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@test.com', 
            'is_active' => false
        ]);
        $managerUser->assignRole('manager');
        
        // Create authenticated user for API calls
        $this->actingAs($adminUser, 'sanctum');
    }

    public function test_can_filter_users_by_role()
    {
        $response = $this->getJson('/api/v1/management/users?role=admin');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message', 
                    'data',
                    'meta' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page'
                    ]
                ]);
                
        $this->assertTrue($response->json('success'));
        $users = $response->json('data');
        $this->assertCount(1, $users);
        $this->assertEquals('Admin User', $users[0]['name']);
    }
    
    public function test_can_filter_users_by_status()
    {
        $response = $this->getJson('/api/v1/management/users?status=false');
        
        $response->assertStatus(200);
        $users = $response->json('data');
        $this->assertCount(1, $users);
        $this->assertEquals('Manager User', $users[0]['name']);
    }
    
    public function test_can_search_users()
    {
        $response = $this->getJson('/api/v1/management/users?search=Admin');
        
        $response->assertStatus(200);
        $users = $response->json('data');
        $this->assertCount(1, $users);
        $this->assertEquals('Admin User', $users[0]['name']);
    }
    
    public function test_can_sort_users()
    {
        $response = $this->getJson('/api/v1/management/users?sort_by=name&sort_order=asc');
        
        $response->assertStatus(200);
        $users = $response->json('data');
        $this->assertCount(3, $users);
        $this->assertEquals('Admin User', $users[0]['name']);
        $this->assertEquals('Manager User', $users[1]['name']);
        $this->assertEquals('Regular User', $users[2]['name']);
    }
    
    public function test_validates_invalid_role_filter()
    {
        $response = $this->getJson('/api/v1/management/users?role=nonexistent');
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['role']);
    }
    
    public function test_validates_invalid_sort_column()
    {
        $response = $this->getJson('/api/v1/management/users?sort_by=invalid_column');
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['sort_by']);
    }
    
    public function test_validates_invalid_sort_order()
    {
        $response = $this->getJson('/api/v1/management/users?sort_order=invalid');
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['sort_order']);
    }
    
    public function test_pagination_works_correctly()
    {
        $response = $this->getJson('/api/v1/management/users?per_page=2&page=1');
        
        $response->assertStatus(200);
        $meta = $response->json('meta');
        $data = $response->json('data');
        $this->assertEquals(2, $meta['per_page']);
        $this->assertEquals(1, $meta['current_page']);
        $this->assertEquals(3, $meta['total']);
        $this->assertCount(2, $data);
    }
}