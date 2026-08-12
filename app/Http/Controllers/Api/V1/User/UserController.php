<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\User;

use App\Enums\UserStatus;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Api\V1\User\ChangeUserStatusRequest;
use App\Http\Requests\Api\V1\User\StoreUserRequest;
use App\Http\Requests\Api\V1\User\UpdateAvatarRequest;
use App\Http\Requests\Api\V1\User\UpdateProfileRequest;
use App\Http\Requests\Api\V1\User\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Query\QueryParameters;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class UserController extends BaseApiController
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $parameters = QueryParameters::fromRequest($request);

        $paginator = $this->userService->index($parameters);

        return $this->paginated(
            UserResource::collection($paginator),
            $paginator,
            __('responses.success'),
        );
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->store(
            $request->validated(),
        );

        return $this->created(
            new UserResource($user),
            __('responses.created'),
        );
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        return $this->success(
            new UserResource(
                $this->userService->show($user)
            ),
            __('responses.success')
        );
    }

    /**
     * Update the specified user.
     */
    public function update(
        UpdateUserRequest $request,
        User $user,
    ): JsonResponse {
        $user = $this->userService->update(
            $user,
            $request->validated(),
        );

        return $this->updated(
            new UserResource($user),
            __('responses.updated'),
        );
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->destroy($user);

        return $this->deleted(
            __('responses.deleted'),
        );
    }

    /**
     * Restore a soft deleted user.
     */
    public function restore(string $user): JsonResponse
    {
        $restoredUser = $this->userService->restore($user);

        return $this->restored(
            new UserResource($restoredUser),
            __('responses.restored'),
        );
    }

    /**
     * Change user status.
     */
    public function changeStatus(
        User $user,
        ChangeUserStatusRequest $request
    ): JsonResponse {
        $updatedUser = $this->userService->changeStatus(
            $user,
            UserStatus::from($request->string('status')->value())
        );

        return $this->statusChanged(
            new UserResource($updatedUser),
            __('responses.status_changed'),
        );
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(
        UpdateProfileRequest $request,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $user = $this->userService->update(
            $user,
            $request->validated(),
        );

        return $this->updated(
            new UserResource($user),
            __('responses.updated'),
        );
    }

    public function updateAvatar(
        UpdateAvatarRequest $request,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var UploadedFile $avatar */
        $avatar = $request->file('avatar');

        $updatedUser = $this->userService->updateAvatar(
            $user,
            $avatar,
        );

        return $this->updated(
            new UserResource($updatedUser),
            __('responses.updated'),
        );
    }
}
