<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;

class UserService
{
    public function create(array $data): User
    {
        $role = Role::where('name', $data['role'])->firstOrFail();

        return User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => $data['password'],
            'role_id'   => $role->id,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(User $user, array $data): User
    {
        if (isset($data['role'])) {
            $role = Role::where('name', $data['role'])->firstOrFail();
            $user->role_id = $role->id;
        }

        $user->fill(collect($data)->except('role')->toArray());
        $user->save();

        return $user->refresh();
    }

    public function deactivate(User $user): User
    {
        $user->update(['is_active' => false]);
        $user->tokens()->delete();

        return $user->refresh();
    }
}