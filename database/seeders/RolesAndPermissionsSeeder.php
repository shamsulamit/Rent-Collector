<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public const PERMISSIONS = [
        'manage properties',
        'manage floors',
        'manage units',
        'manage tenants',
        'manage tenancies',
        'manage leases',
        'manage meters',
        'manage readings',
        'generate bills',
        'finalize bills',
        'record payments',
        'allocate payments',
        'manage expenses',
        'manage vendors',
        'manage maintenance',
        'manage documents',
        'view reports',
        'manage users',
        'manage backups',
        'restore backups',
        'close accounting periods',
        'manage settings',
        'manage tariffs',
        'view audit logs',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roleMatrix = [
            'owner' => self::PERMISSIONS,
            'manager' => [
                'manage properties', 'manage floors', 'manage units', 'manage tenants',
                'manage tenancies', 'manage leases', 'manage meters', 'manage readings',
                'generate bills', 'finalize bills', 'record payments', 'allocate payments',
                'manage expenses', 'manage vendors', 'manage maintenance', 'manage documents',
                'view reports', 'manage tariffs',
            ],
            'accountant' => [
                'generate bills', 'finalize bills', 'record payments', 'allocate payments',
                'manage expenses', 'view reports',
            ],
            'staff' => [
                'manage readings', 'record payments', 'manage maintenance', 'manage documents',
            ],
        ];

        foreach ($roleMatrix as $role => $permissions) {
            $roleModel = Role::firstOrCreate(['name' => $role]);
            $roleModel->syncPermissions($permissions);
        }
    }
}
