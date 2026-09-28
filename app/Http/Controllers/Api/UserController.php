<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('role')
            ->when($request->filled('search'), fn ($q) =>
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%'))
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return UserResource::collection($users)->response();
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->service->create($request->validated());
        return (new UserResource($user->load('role')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        return (new UserResource($user->load('role')))->response();
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->service->update($user, $request->validated());
        return (new UserResource($user->load('role')))->response();
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        $this->service->deactivate($user);

        return response()->json(['message' => 'Utilisateur désactivé.']);
    }

    public function profile(Request $request): JsonResponse
    {
        return (new UserResource($request->user()->load('role.permissions')))->response();
    }
}