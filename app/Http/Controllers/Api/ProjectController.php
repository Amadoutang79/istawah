<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\AssignMemberRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        $user = $request->user();

        $query = Project::with(['creator', 'members']);

        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('members', fn ($m) => $m->where('user_id', $user->id));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return ProjectResource::collection(
            $query->orderByDesc('id')->paginate($request->integer('per_page', 15))
        )->response();
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->service->create($request->user(), $request->validated());

        return (new ProjectResource($project->load(['creator', 'members'])))
            ->response()->setStatusCode(201);
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return (new ProjectResource($project->load(['creator', 'members'])))->response();
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $project = $this->service->update($project, $request->validated());

        return (new ProjectResource($project->load(['creator', 'members'])))->response();
    }

    public function archive(Project $project): JsonResponse
    {
        $this->authorize('archive', $project);
        $project = $this->service->archive($project);

        return response()->json([
            'message' => 'Projet archivé.',
            'project' => new ProjectResource($project),
        ]);
    }

    public function members(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return response()->json([
            'members' => $project->members->map(fn ($u) => [
                'id'           => $u->id,
                'name'         => $u->name,
                'email'        => $u->email,
                'project_role' => $u->pivot->project_role,
            ]),
        ]);
    }

    public function assignMember(AssignMemberRequest $request, Project $project): JsonResponse
    {
        $project = $this->service->assignMember(
            $project,
            (int) $request->input('user_id'),
            $request->input('project_role')
        );

        return response()->json([
            'message' => 'Membre affecté.',
            'project' => new ProjectResource($project->load('members')),
        ]);
    }

    public function removeMember(Project $project, User $user): JsonResponse
    {
        $this->authorize('assignMembers', $project);
        $this->service->removeMember($project, $user->id);

        return response()->json(['message' => 'Membre retiré.']);
    }
}