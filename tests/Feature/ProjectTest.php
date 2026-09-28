<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_manager_can_create_project(): void
    {
        $manager = User::factory()->create([
            'role_id' => Role::where('name', 'MANAGER')->first()->id,
        ]);
        Sanctum::actingAs($manager);

        $res = $this->postJson('/api/projects', [
            'name'        => 'Projet Alpha',
            'description' => 'Démo',
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('data.name', 'Projet Alpha');
    }

    public function test_project_is_protected_from_other_users(): void
    {
        $owner = User::factory()->create([
            'role_id' => Role::where('name', 'MANAGER')->first()->id,
        ]);
        $other = User::factory()->create([
            'role_id' => Role::where('name', 'USER')->first()->id,
        ]);

        Sanctum::actingAs($owner);
        $create = $this->postJson('/api/projects', ['name' => 'Secret'])->json();
        $projectId = $create['data']['id'];

        Sanctum::actingAs($other);
        $this->getJson("/api/projects/{$projectId}")->assertStatus(403);
    }

    public function test_project_validation_fails(): void
    {
        $manager = User::factory()->create([
            'role_id' => Role::where('name', 'MANAGER')->first()->id,
        ]);
        Sanctum::actingAs($manager);

        $this->postJson('/api/projects', ['description' => 'no name'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_assign_member_to_project(): void
    {
        $manager = User::factory()->create([
            'role_id' => Role::where('name', 'MANAGER')->first()->id,
        ]);
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'USER')->first()->id,
        ]);

        Sanctum::actingAs($manager);
        $project = $this->postJson('/api/projects', ['name' => 'Team'])->json('data');

        $res = $this->postJson("/api/projects/{$project['id']}/members", [
            'user_id'      => $user->id,
            'project_role' => 'MEMBER',
        ]);

        $res->assertOk();
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project['id'],
            'user_id'    => $user->id,
            'project_role' => 'MEMBER',
        ]);
    }
}