<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Role;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Api\V1\Role\StoreRoleRequest;
use App\Http\Requests\Api\V1\Role\SyncRolePermissionsRequest;
use App\Http\Requests\Api\V1\Role\UpdateRoleRequest;
use App\Http\Resources\Api\V1\PermissionResource;
use App\Http\Resources\Api\V1\RoleResource;
use App\Query\QueryParameters;
use App\Services\Role\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends BaseApiController
{
    public function __construct(
        private readonly RoleService $roleService,
    ) {}

    /**
     * Display a listing of roles.
     */
    public function index(Request $request): JsonResponse
    {
        $parameters = QueryParameters::fromRequest($request);

        $paginator = $this->roleService->index($parameters);

        return $this->paginated(
            RoleResource::collection($paginator),
            $paginator,
            __('responses.success'),
        );
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): JsonResponse
    {
        return $this->success(
            new RoleResource(
                $this->roleService->show($role)
            ),
            __('responses.success'),
        );
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->store(
            $request->validated(),
        );

        return $this->created(
            new RoleResource($role),
            __('responses.created'),
        );
    }

    /**
     * Update the specified role.
     */
    public function update(
        UpdateRoleRequest $request,
        Role $role,
    ): JsonResponse {
        $role = $this->roleService->update(
            $role,
            $request->validated(),
        );

        return $this->updated(
            new RoleResource($role),
            __('responses.updated'),
        );
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->roleService->destroy($role);

        return $this->deleted(
            __('responses.deleted'),
        );
    }

    public function permissions(Role $role): JsonResponse
    {
        return $this->success(
            PermissionResource::collection(
                $this->roleService->permissions($role),
            ),
            __('responses.success'),
        );
    }

    public function syncPermissions(
        SyncRolePermissionsRequest $request,
        Role $role,
    ): JsonResponse {
        $role = $this->roleService->syncPermissions(
            $role,
            $request->validated('permissions'),
        );

        return $this->updated(
            new RoleResource($role),
            __('responses.updated'),
        );
    }
}
