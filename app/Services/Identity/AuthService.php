<?php

namespace App\Services\Identity;

use App\Exceptions\AccountDisabledException;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * @return array{user: User, token: string, expires_at: string|null}
     *
     * @throws InvalidCredentialsException
     * @throws AccountDisabledException
     */
    public function login(string $email, string $password): array
    {
        $user = User::query()->where('email', $email)->first();

        // Se compara siempre contra un hash para no revelar si el correo existe.
        $hash = $user?->password ?? '$2y$12$usesomesillystringforsaltfoeP5sJ5ZCvDk0ob6dUvL1G0mV6Q2e';

        if (! Hash::check($password, $hash) || $user === null) {
            throw new InvalidCredentialsException;
        }

        if (! $user->is_active) {
            throw new AccountDisabledException;
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $result = $user->createToken('spa');

        return [
            'user' => $user,
            'token' => $result->accessToken,
            'expires_at' => $result->token->expires_at?->toIso8601String(),
        ];
    }

    public function logout(User $user): void
    {
        $user->token()?->revoke();
    }
}
