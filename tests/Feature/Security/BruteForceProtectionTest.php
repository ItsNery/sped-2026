<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BruteForceProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_deactivated_after_3_failed_login_attempts()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // 1st failed attempt
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $user->refresh();
        $this->assertEquals(1, $user->failed_login_attempts);
        $this->assertTrue((bool)$user->is_active);

        // 2nd failed attempt
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $user->refresh();
        $this->assertEquals(2, $user->failed_login_attempts);
        $this->assertTrue((bool)$user->is_active);

        // 3rd failed attempt
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $user->refresh();
        $this->assertEquals(3, $user->failed_login_attempts);
        $this->assertFalse((bool)$user->is_active);

        // Correct password should fail now
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_failed_login_attempts_are_recorded_in_database()
    {
        $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('login_attempts', [
            'email' => 'nonexistent@example.com',
            'status' => 'failure',
        ]);
    }

    public function test_successful_login_resets_failed_attempts()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'failed_login_attempts' => 2,
        ]);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $user->refresh();
        $this->assertEquals(0, $user->failed_login_attempts);
        $this->assertDatabaseHas('login_attempts', [
            'email' => 'test@example.com',
            'status' => 'success',
        ]);
    }

    public function test_registration_is_disabled()
    {
        $response = $this->get('/register');

        // Fortify might return 404 or redirect depending on setup, but typically it returns 404 if disabled
        $response->assertStatus(404);
    }
}
