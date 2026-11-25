<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Perfil + permisos del usuario autenticado.
 * "permissions" son los nombres planos y "abilities" el mismo dato en formato
 * { action, subject } (compatible con CASL, pero la SPA puede usar solo "permissions").
 *
 * @mixin User
 */
class MeResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $permissions = $this->getAllPermissions()->pluck('name')->sort()->values();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'roles' => $this->getRoleNames()->values(),
            'permissions' => $permissions,
            'abilities' => $permissions->map(static function (string $permission): array {
                [$subject, $action] = array_pad(explode('.', $permission, 2), 2, 'manage');

                return ['action' => $action, 'subject' => $subject];
            })->values(),
        ];
    }
}
