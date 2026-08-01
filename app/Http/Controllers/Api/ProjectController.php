<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projectService) {}

    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Project::class);

        return $this->success(
            ProjectResource::collection($this->projectService->paginateForUser(request()->user())),
            'Projects retrieved successfully.'
        );
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        Gate::authorize('create', Project::class);

        $project = $this->projectService->createForUser(
            $request->user(),
            $request->validated()
        );

        return $this->success(new ProjectResource($project), 'Project created successfully.', 201);
    }

    public function show(Project $project): JsonResponse
    {
        Gate::authorize('view', $project);

        return $this->success(new ProjectResource($project), 'Project retrieved successfully.');
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        Gate::authorize('update', $project);

        $project = $this->projectService->update($project, $request->validated());

        return $this->success(new ProjectResource($project), 'Project updated successfully.');
    }

    public function destroy(Project $project): JsonResponse
    {
        Gate::authorize('delete', $project);

        $this->projectService->delete($project);

        return $this->success(message: 'Project deleted successfully.');
    }
}
