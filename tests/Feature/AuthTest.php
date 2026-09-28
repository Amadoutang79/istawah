<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_register_success(): void
    {
        $res = $this->postJson('/api/auth/register', [
            'name'                  => 'Amadou',
            'email'                 => 'amadou@test.dev',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $res->assertStatus(201)
            ->assertJsonStructure(['message', 'user' => ['id', 'email'], 'token']);
    }

    public function test_login_success(): void
    {
        $role = Role::where('name', 'USER')->first();
        User::factory()->create([
            'email'    => 'u@test.dev',
            'password' => 'Password@123',
            'role_id'  => $role->id,
        ]);

        $res = $this->postJson('/api/auth/login', [
            'email'    => 'u@test.dev',
            'password' => 'Password@123',
        ]);

        $res->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_login_failed_with_bad_password(): void
    {
        $role = Role::where('name', 'USER')->first();
        User::factory()->create([
            'email'    => 'u2@test.dev',
            'password' => 'Password@123',
            'role_id'  => $role->id,
        ]);

        $res = $this->postJson('/api/auth/login', [
            'email'    => 'u2@test.dev',
            'password' => 'wrong-password',
        ]);

        $res->assertStatus(422);
    }
}