<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'password'   => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success', 'message',
                'data' => ['token', 'user' => ['id', 'first_name', 'last_name', 'email']],
            ])
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com', 'role' => 'user']);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'jane@example.com']);

        $response = $this->postJson('/api/v1/register', [
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
            'email'      => 'jane@example.com',
            'password'   => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_register_fails_with_missing_fields(): void
    {
        $response = $this->postJson('/api/v1/register', ['email' => 'test@example.com']);

        $response->assertStatus(422)
            ->assertJsonStructure(['success', 'message', 'errors']);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email'    => 'user@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email'    => 'user@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['token', 'user'],
            ]);
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $response = $this->postJson('/api/v1/login', [
            'email'    => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_authenticated_user_can_fetch_profile(): void
    {
        $user = $this->actingAsUser();

        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_user_can_logout(): void
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Successfully logged out']);
    }
}
