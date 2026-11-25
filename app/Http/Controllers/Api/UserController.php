<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Identity\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->users->paginate(
            max(1, min(100, $request->integer('per_page', 20))),
            $request->filled('search') ? $request->string('search')->toString() : null,
        );

        return UserResource::collection($paginator);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->users->create($request->validated());

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->load('roles:id,name'));
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        return new UserResource($this->users->update($user, $request->validated(), $request->user()));
    }

    public function activate(Request $request, User $user): UserResource
    {
        return new UserResource($this->users->setActive($user, true, $request->user()));
    }

    public function deactivate(Request $request, User $user): UserResource
    {
        return new UserResource($this->users->setActive($user, false, $request->user()));
    }
}
