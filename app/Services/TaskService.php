<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    /**
     * @param  array{status?: string, priority?: string, search?: string}  $filters
     */
    public function paginateForProject(Project $project, array $filters): LengthAwarePaginator
    {
        return $project->tasks()
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($query, string $priority) => $query->where('priority', $priority))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @param  array{title: string, description?: string|null, priority?: string, status?: string, due_date?: string|null}  $data
     */
    public function createForProject(Project $project, array $data): Task
    {
        return $project->tasks()->create($data);
    }

    /**
     * @param  array{title?: string, description?: string|null, priority?: string, status?: string, due_date?: string|null}  $data
     */
    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
