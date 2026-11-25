<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\MeResource;
use App\Services\Identity\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login($request->string('email')->toString(), $request->string('password')->toString());

        return response()->json(['data' => [
            'token_type' => 'Bearer',
            'access_token' => $result['token'],
            'expires_at' => $result['expires_at'],
            'user' => (new MeResource($result['user']))->resolve($request),
        ]]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->user());

        return response()->json(['data' => ['message' => 'Sesión cerrada.']]);
    }

    public function me(Request $request): MeResource
    {
        return new MeResource($request->user());
    }
}
