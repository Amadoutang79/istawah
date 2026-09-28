<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $auth): bool
    {
        return $auth->hasPermission('projects.view');
    }

    public function view(User $auth, Project $project): bool
    {
        if ($auth->isAdmin()) return true;
        if ($project->created_by === $auth->id) return true;

        return $project->members()->where('user_id', $auth->id)->exists();
    }

    public function create(User $auth): bool
    {
        return $auth->hasPermission('projects.create');
    }

    public function update(User $auth, Project $project): bool
    {
        if ($auth->isAdmin()) return true;
        if ($project->created_by === $auth->id) return true;

        return $project->userRole($auth) === 'MANAGER';
    }

    public function archive(User $auth, Project $project): bool
    {
        return $auth->isAdmin() || $project->created_by === $auth->id;
    }

    public function assignMembers(User $auth, Project $project): bool
    {
        return $auth->isAdmin()
            || $project->created_by === $auth->id
            || $project->userRole($auth) === 'MANAGER';
    }
}