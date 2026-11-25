<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    private const GUARD = 'api';

    /** @var array<string, list<string>> rol => permisos */
    private const ROLES = [
        'Admin' => [
            'competitions.view', 'competitions.import', 'teams.view', 'players.view', 'users.manage', 'roles.manage',
        ],
        'Editor' => ['competitions.view', 'competitions.import', 'teams.view', 'players.view'],
        'Viewer' => ['competitions.view', 'teams.view', 'players.view'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $all = collect(self::ROLES)->flatten()->unique();

        foreach ($all as $name) {
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => self::GUARD]);
        }

        foreach (self::ROLES as $roleName => $permissions) {
            Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => self::GUARD])
                ->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
