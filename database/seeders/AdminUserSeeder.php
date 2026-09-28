<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'ADMIN')->first();

        User::updateOrCreate(
            ['email' => 'admin@istawah.test'],
            [
                'name'      => 'Admin ISTAWAH',
                'password'  => 'Password@123',
                'role_id'   => $adminRole?->id,
                'is_active' => true,
            ]
        );
    }
}