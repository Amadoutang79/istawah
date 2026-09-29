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

    /**
     * Mise à jour d'un utilisateur.
     *
     * Défense en profondeur : même si la Policy et le FormRequest
     * ont déjà filtré, on vérifie ICI (au niveau métier) que seul
     * un ADMIN peut modifier le champ `role`.
     *
     * Si un appelant non-admin tente de passer `role`, le champ est
     * silencieusement ignoré (pas d'erreur, pas de fuite d'info).
     */
    public function update(User $user, array $data): User
    {
        $auth = auth()->user();

        // Anti-élévation de privilèges : seul un ADMIN peut changer un rôle
        if (isset($data['role'])) {
            if (! $auth || ! $auth->isAdmin()) {
                // On ignore silencieusement le champ `role`
                unset($data['role']);
            } else {
                $role = Role::where('name', $data['role'])->firstOrFail();
                $user->role_id = $role->id;
            }
        }

        $user->fill(collect($data)->except('role')->toArray());
        $user->save();

        return $user->refresh();
    }

    /**
     * Désactivation d'un utilisateur : passage en `is_active = false`
     * et suppression de tous ses tokens (déconnexion forcée).
     */
    public function deactivate(User $user): User
    {
        $user->update(['is_active' => false]);
        $user->tokens()->delete();

        return $user->refresh();
    }
}