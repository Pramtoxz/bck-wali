<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'approve field duty']);
        Permission::create(['name' => 'approve leave']);
        Permission::create(['name' => 'view reports']);
        Permission::create(['name' => 'submit field duty']);
        Permission::create(['name' => 'submit leave']);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'manage users',
            'approve field duty',
            'approve leave',
            'view reports',
            'submit field duty',
            'submit leave',
        ]);

        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo([
            'submit field duty',
            'submit leave',
        ]);
    }
}
