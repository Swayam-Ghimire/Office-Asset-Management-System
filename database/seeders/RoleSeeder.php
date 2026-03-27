<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::create(['name' => 'admin']);
        $employee = Role::create(['name' => 'employee']);

        // Permissions
        $permissions = [
            'manage users',
            'manage assets',
            'approve requests',
            'request asset',
            'view all assets',
            'view own assets',
            'report issues',
            'view maintenance report',
            'manage categories',
            'view logs',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $admin->givePermissionTo(['manage users', 'manage assets', 'approve requests', 'view all assets', 'manage categories', 'view logs']);
        $employee->givePermissionTo(['request asset', 'view own assets', 'report issues', 'view maintenance report']);

    }
}
