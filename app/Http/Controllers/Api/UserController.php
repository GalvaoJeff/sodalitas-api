<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\FollowService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly FollowService $followService,
    ) {}

    public function show(Request $request, string $username): JsonResponse
    {
        $user = $this->userService->findProfile($username, $request->user());

        return response()->json(['user' => new UserResource($user)]);
    }

    public function update(UpdateUserRequest $request): JsonResponse
    {
        $user = $this->userService->updateProfile(
            $request->user(),
            $request->safe()->except('avatar'),
            $request->file('avatar')
        );

        return response()->json(['user' => new UserResource($user)]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:1']]);

        $users = $this->userService->search($request->string('q'));

        return response()->json(['users' => UserResource::collection($users)]);
    }

    public function toggleFollow(Request $request, string $username): JsonResponse
    {
        $target = User::where('username', $username)->firstOrFail();

        $isFollowing = $this->followService->toggle($request->user(), $target);

        return response()->json(['is_following' => $isFollowing]);
    }
}
