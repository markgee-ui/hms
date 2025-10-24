<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }

    /**
     * Test admin login helper method
     */
    public function test_login_as_doctor(): void
    {
        // Create test admin user
        $doctor = User::factory()->create([
            'role' => 'doctor'
        ]);

        $this->loginAsDoctor();
        
        $this->assertAuthenticatedAs($doctor);
    }

    /**
     * Test user authentication
     */
    public function test_user_can_authenticate(): void 
    {
        $user = User::factory()->create();
        
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $this->assertAuthenticated();
    }

    /**
     * Test admin access restriction
     */
    public function test_non_admin_cannot_access_admin_route(): void
    {
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        $this->actingAs($user);

        $response = $this->get('/admin/dashboard');
        
        $response->assertStatus(403);
    }

    public function loginAsAdmin()
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);
    }

    public function loginAsDoctor()
    {
        $doctor = User::where('role', 'doctor')->first();
        $this->actingAs($doctor);
    }
}