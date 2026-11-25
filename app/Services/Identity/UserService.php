<?php

namespace App\Services\Identity;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function paginate(int $perPage, ?string $search): LengthAwarePaginator
    {
        return User::query()
            ->with('roles:id,name')
            ->when($search, function ($query, string $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $query->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    /** @param  array{name: string, email: string, password: string, is_active?: bool, roles?: list<string>}  $data */
    public function create(array $data): User
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        $user->syncRoles($data['roles'] ?? []);

        return $user->load('roles:id,name');
    }

    /** @param  array{name?: string, email?: string, password?: string|null, is_active?: bool, roles?: list<string>}  $data */
    public function update(User $user, array $data, User $actor): User
    {
        if (array_key_exists('is_active', $data) && ! $data['is_active']) {
            $this->guardNotSelf($user, $actor);
        }

        $attributes = array_filter(
            array_intersect_key($data, array_flip(['name', 'email', 'password', 'is_active'])),
            static fn ($value): bool => $value !== null,
        );
        $user->update($attributes);

        if (array_key_exists('roles', $data)) {
            $user->syncRoles($data['roles']);
        }

        if (isset($attributes['is_active']) && ! $attributes['is_active']) {
            $this->revokeTokens($user);
        }

        return $user->load('roles:id,name');
    }

    public function setActive(User $user, bool $active, User $actor): User
    {
        if (! $active) {
            $this->guardNotSelf($user, $actor);
        }

        $user->update(['is_active' => $active]);

        if (! $active) {
            $this->revokeTokens($user); // la sesión del usuario desactivado muere de inmediato
        }

        return $user->load('roles:id,name');
    }

    private function guardNotSelf(User $user, User $actor): void
    {
        if ($user->is($actor)) {
            throw ValidationException::withMessages(['user' => 'No puedes desactivar tu propia cuenta.']);
        }
    }

    private function revokeTokens(User $user): void
    {
        $user->tokens()->get()->each->revoke();
    }
}
