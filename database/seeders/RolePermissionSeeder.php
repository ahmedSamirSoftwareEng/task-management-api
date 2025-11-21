<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $user    = Role::firstOrCreate(['name' => 'user']);

        $permissions = [
            'create tasks',
            'edit tasks',
            'view tasks',
            'delete tasks',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $manager->givePermissionTo([
            'create tasks',
            'edit tasks',
            'view tasks',
            'delete tasks',
        ]);
        $user->givePermissionTo([
            'view tasks',
            'edit tasks',
        ]);
    }
}
