<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'admin.access',
            'users.manage',
            'reports.manage',
            'reports.publish',
            'plans.manage',
            'billing.manage',
            'reports.read',
            'reports.download',
            'dashboard.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $user = Role::firstOrCreate(['name' => 'user']);

        $admin->syncPermissions(Permission::all());

        $user->syncPermissions([
            'reports.read',
        ]);
    }
}
