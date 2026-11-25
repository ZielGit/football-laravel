<?php

namespace App\Services\Identity;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    public const GUARD = 'api';

    public const PROTECTED_ROLE = 'Admin';

    /** @return Collection<int, Role> */
    public function all(): Collection
    {
        return Role::query()->where('guard_name', self::GUARD)->with('permissions:id,name')->withCount('users')->orderBy('name')->get();
    }

    /** @return Collection<int, Permission> */
    public function permissions(): Collection
    {
        return Permission::query()->where('guard_name', self::GUARD)->orderBy('name')->get();
    }

    /** @param  array{name: string, permissions?: list<string>}  $data */
    public function create(array $data): Role
    {
        $role = Role::query()->create(['name' => $data['name'], 'guard_name' => self::GUARD]);
        $role->syncPermissions($data['permissions'] ?? []);

        return $role->load('permissions:id,name')->loadCount('users');
    }

    /** @param  array{name?: string, permissions?: list<string>}  $data */
    public function update(Role $role, array $data): Role
    {
        if ($role->name === self::PROTECTED_ROLE) {
            throw ValidationException::withMessages(['role' => 'El rol Admin no se puede modificar.']);
        }

        if (isset($data['name'])) {
            $role->update(['name' => $data['name']]);
        }

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions:id,name')->loadCount('users');
    }

    public function delete(Role $role): void
    {
        if ($role->name === self::PROTECTED_ROLE) {
            throw ValidationException::withMessages(['role' => 'El rol Admin no se puede eliminar.']);
        }

        if ($role->users()->exists()) {
            throw ValidationException::withMessages(['role' => 'No se puede eliminar un rol que tiene usuarios asignados.']);
        }

        $role->delete();
    }
}
