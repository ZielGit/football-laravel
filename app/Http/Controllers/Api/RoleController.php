<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Services\Identity\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roles) {}

    public function index(): AnonymousResourceCollection
    {
        return RoleResource::collection($this->roles->all());
    }

    public function permissions(): AnonymousResourceCollection
    {
        return PermissionResource::collection($this->roles->permissions());
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        return (new RoleResource($this->roles->create($request->validated())))->response()->setStatusCode(201);
    }

    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        return new RoleResource($this->roles->update($role, $request->validated()));
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->roles->delete($role);

        return response()->json(null, 204);
    }
}
