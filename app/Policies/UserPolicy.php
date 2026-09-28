<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $auth): bool
    {
        return $auth->hasPermission('users.view');
    }

    public function view(User $auth, User $target): bool
    {
        return $auth->id === $target->id || $auth->hasPermission('users.view');
    }

    public function create(User $auth): bool
    {
        return $auth->hasPermission('users.create');
    }

    public function update(User $auth, User $target): bool
    {
        if ($auth->id === $target->id) return true;
        return $auth->hasPermission('users.update');
    }

    public function delete(User $auth, User $target): bool
    {
        return $auth->id !== $target->id && $auth->hasPermission('users.delete');
    }
}