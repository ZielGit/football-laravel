<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/** Usuarios de prueba documentados en el README. */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['Administrador', 'admin@football.test', 'Admin123!', 'Admin'],
            ['Editor', 'editor@football.test', 'Editor123!', 'Editor'],
            ['Solo consulta', 'viewer@football.test', 'Viewer123!', 'Viewer'],
        ];

        foreach ($users as [$name, $email, $password, $role]) {
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => $password, 'is_active' => true],
            );

            $user->syncRoles([$role]);
        }
    }
}
