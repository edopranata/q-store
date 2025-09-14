<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;
use Tests\TestCase;

class UserEndpointEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'user', 'guard_name' => 'web']);
        
        // Create authenticated user for API calls
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'is_active' => true
        ]);
        $adminUser->assignRole('admin');
        
        $this->actingAs($adminUser, 'sanctum');
    }

    public function test_handles_empty_role_filter()
    {
        $response = $this->getJson('/api/v1/management/users?role=');
        
        $response->assertStatus(200);
        // Should return all users when role filter is empty
        $this->assertTrue($response->json('success'));
        // At least one user should exist (the admin user from setUp)
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }
    
    public function test_handles_invalid_per_page_values()
    {
        // Test negative per_page
        $response = $this->getJson('/api/v1/management/users?per_page=-1');
        $response->assertStatus(422);
        
        // Test zero per_page
        $response = $this->getJson('/api/v1/management/users?per_page=0');
        $response->assertStatus(422);
        
        // Test extremely large per_page (should be capped)
        $response = $this->getJson('/api/v1/management/users?per_page=1000');
        $response->assertStatus(422);
    }
    
    public function test_handles_invalid_page_values()
    {
        // Test negative page
        $response = $this->getJson('/api/v1/management/users?page=-1');
        $response->assertStatus(422);
        
        // Test zero page
        $response = $this->getJson('/api/v1/management/users?page=0');
        $response->assertStatus(422);
    }
    
    public function test_handles_invalid_date_formats()
    {
        // Test invalid created_from date
        $response = $this->getJson('/api/v1/management/users?created_from=invalid-date');
        $response->assertStatus(422);
        
        // Test invalid date range (created_to before created_from)
        $response = $this->getJson('/api/v1/management/users?created_from=2024-01-15&created_to=2024-01-10');
        $response->assertStatus(422);
    }
    
    public function test_handles_sql_injection_attempts()
    {
        // Test SQL injection in search parameter
        $response = $this->getJson('/api/v1/management/users?search=' . urlencode("'; DROP TABLE users; --"));
        $response->assertStatus(200); // Should not cause error, just return empty results
        $this->assertTrue($response->json('success'));
        
        // Test SQL injection in role parameter
        $response = $this->getJson('/api/v1/management/users?role=' . urlencode("admin'; DROP TABLE roles; --"));
        $response->assertStatus(422); // Should be caught by validation
        $this->assertFalse($response->json('success'));
    }
    
    public function test_handles_very_long_search_strings()
    {
        $longString = str_repeat('a', 300); // Longer than max 255
        $response = $this->getJson('/api/v1/management/users?search=' . $longString);
        $response->assertStatus(422);
    }
    
    public function test_handles_special_characters_in_search()
    {
        // Test search with special characters
        $response = $this->getJson('/api/v1/management/users?search=' . urlencode('test@#$%^&*()'));
        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }
    
    public function test_handles_unicode_characters_in_search()
    {
        // Test search with unicode characters
        $response = $this->getJson('/api/v1/management/users?search=' . urlencode('测试用户'));
        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }
    
    public function test_handles_multiple_filters_combination()
    {
        // Test combining multiple filters
        $response = $this->getJson('/api/v1/management/users?role=admin&status=true&search=Admin&sort_by=name&sort_order=asc&per_page=5');
        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }
    
    public function test_handles_case_sensitivity_in_filters()
    {
        // Test case sensitivity in role filter - should return 200 as role validation only checks string format
        $response = $this->getJson('/api/v1/management/users?role=ADMIN');
        $response->assertStatus(200); // Should work as role validation only checks string format
        $this->assertTrue($response->json('success'));
        
        // Test case sensitivity in sort_order
        $response = $this->getJson('/api/v1/management/users?sort_order=ASC');
        $response->assertStatus(200); // Should work as validation converts to lowercase
        $this->assertTrue($response->json('success'));
    }
    
    public function test_performance_with_large_dataset()
    {
        // Create many users to test performance
        User::factory()->count(50)->create();
        
        $startTime = microtime(true);
        $response = $this->getJson('/api/v1/management/users?per_page=20');
        $endTime = microtime(true);
        
        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        
        // Assert response time is reasonable (less than 1 second)
        $this->assertLessThan(1.0, $endTime - $startTime);
    }
    
    public function test_consistent_response_format()
    {
        $response = $this->getJson('/api/v1/management/users');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'username',
                            'is_active',
                            'created_at',
                            'updated_at',
                            'roles'
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page',
                        'from',
                        'to',
                        'has_more_pages'
                    ]
                ]);
    }
}