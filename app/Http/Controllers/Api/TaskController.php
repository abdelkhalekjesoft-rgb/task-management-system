<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\ListTasksRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService) {}

    public function index(ListTasksRequest $request, Project $project): JsonResponse
    {
        Gate::authorize('viewAny', [Task::class, $project]);

        return $this->success(
            TaskResource::collection($this->taskService->paginateForProject($project, $request->validated())),
            'Tasks retrieved successfully.'
        );
    }

    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        Gate::authorize('create', [Task::class, $project]);

        $task = $this->taskService->createForProject($project, $request->validated());

        return $this->success(new TaskResource($task), 'Task created successfully.', 201);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        Gate::authorize('update', $task);

        $task = $this->taskService->update($task, $request->validated());

        return $this->success(new TaskResource($task), 'Task updated successfully.');
    }

    public function destroy(Task $task): JsonResponse
    {
        Gate::authorize('delete', $task);

        $this->taskService->delete($task);

        return $this->success(message: 'Task deleted successfully.');
    }
}
