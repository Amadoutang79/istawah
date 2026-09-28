<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', 'ADMIN')->first()->id,
        ]);

        Sanctum::actingAs($admin);

        $res = $this->postJson('/api/users', [
            'name'                  => 'Manager 1',
            'email'                 => 'm1@test.dev',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role'                  => 'MANAGER',
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('data.role.name', 'MANAGER');
    }

    public function test_user_cannot_create_user(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'USER')->first()->id,
        ]);

        Sanctum::actingAs($user);

        $res = $this->postJson('/api/users', [
            'name'                  => 'X',
            'email'                 => 'x@test.dev',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role'                  => 'ADMIN',
        ]);

        $res->assertStatus(403);
    }

    public function test_admin_route_allowed_for_admin(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', 'ADMIN')->first()->id,
        ]);
        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/ping')->assertOk();
    }

    public function test_admin_route_forbidden_for_user(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'USER')->first()->id,
        ]);
        Sanctum::actingAs($user);

        $this->getJson('/api/admin/ping')->assertStatus(403);
    }

    public function test_user_cannot_self_assign_privileged_role(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'USER')->first()->id,
        ]);
        Sanctum::actingAs($user);

        $res = $this->putJson("/api/users/{$user->id}", ['role' => 'ADMIN']);
        $res->assertStatus(403);
    }
}