<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function create(User $creator, array $data): Project
    {
        return DB::transaction(function () use ($creator, $data) {
            $project = Project::create([
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'created_by'  => $creator->id,
                'status'      => 'active',
            ]);

            // Le créateur devient OWNER
            $project->members()->attach($creator->id, ['project_role' => 'OWNER']);

            return $project;
        });
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);
        return $project->refresh();
    }

    public function archive(Project $project): Project
    {
        $project->update([
            'status'      => 'archived',
            'archived_at' => now(),
        ]);
        return $project->refresh();
    }

    public function assignMember(Project $project, int $userId, string $role): Project
    {
        $project->members()->syncWithoutDetaching([
            $userId => ['project_role' => $role],
        ]);
        return $project->refresh();
    }

    public function removeMember(Project $project, int $userId): void
    {
        $project->members()->detach($userId);
    }
}