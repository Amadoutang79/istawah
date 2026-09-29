<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Voir la liste des utilisateurs (route index).
     */
    public function viewAny(User $auth): bool
    {
        return $auth->hasPermission('users.view');
    }

    /**
     * Voir un utilisateur : soit le sien, soit permission users.view.
     */
    public function view(User $auth, User $target): bool
    {
        return $auth->id === $target->id
            || $auth->hasPermission('users.view');
    }

    /**
     * Créer un utilisateur : nécessite users.create.
     */
    public function create(User $auth): bool
    {
        return $auth->hasPermission('users.create');
    }

    /**
     * Modifier un utilisateur.
     *
     * - Sur son propre profil : autorisé,
     *   MAIS interdiction absolue de modifier le champ `role`
     *   pour un non-admin (anti-élévation de privilèges).
     * - Sur le profil d'un autre : permission users.update requise.
     */
    public function update(User $auth, User $target): bool
    {
        // Cas 1 : l'utilisateur modifie son propre compte
        if ($auth->id === $target->id) {
            // Anti-élévation : si la requête contient un champ `role`,
            // seul un ADMIN peut passer.
            if (request()->has('role') && ! $auth->isAdmin()) {
                return false;
            }
            return true;
        }

        // Cas 2 : modification d'un autre utilisateur
        return $auth->hasPermission('users.update');
    }

    /**
     * Désactiver un utilisateur.
     *
     * On ne peut pas se désactiver soi-même (anti-lockout)
     * et il faut la permission users.delete.
     */
    public function delete(User $auth, User $target): bool
    {
        return $auth->id !== $target->id
            && $auth->hasPermission('users.delete');
    }
}