<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users.view'      => 'Voir les utilisateurs',
            'users.create'    => 'Créer un utilisateur',
            'users.update'    => 'Modifier un utilisateur',
            'users.delete'    => 'Désactiver un utilisateur',
            'projects.view'   => 'Voir les projets',
            'projects.create' => 'Créer un projet',
            'projects.update' => 'Modifier un projet',
            'projects.archive'=> 'Archiver un projet',
            'projects.assign' => 'Affecter des membres',
        ];

        foreach ($permissions as $name => $label) {
            Permission::updateOrCreate(['name' => $name], ['label' => $label]);
        }

        $roles = [
            'ADMIN'   => ['label' => 'Administrateur', 'perms' => array_keys($permissions)],
            'MANAGER' => [
                'label' => 'Manager',
                'perms' => [
                    'users.view',
                    'projects.view','projects.create','projects.update',
                    'projects.archive','projects.assign',
                ],
            ],
            'USER' => [
                'label' => 'Utilisateur',
                'perms' => ['projects.view'],
            ],
        ];

        foreach ($roles as $name => $data) {
            $role = Role::updateOrCreate(['name' => $name], ['label' => $data['label']]);
            $ids  = Permission::whereIn('name', $data['perms'])->pluck('id');
            $role->permissions()->sync($ids);
        }
    }
}