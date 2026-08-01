<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\User;

class DashboardService
{
    /**
     * @return array<string, int>
     */
    public function statsForUser(User $user): array
    {
        $projects = $user->projects();
        $tasks = $user->projects()
            ->join('tasks', 'projects.id', '=', 'tasks.project_id');

        return [
            'total_projects' => (clone $projects)->count(),
            'active_projects' => (clone $projects)->where('status', ProjectStatus::Active)->count(),
            'total_tasks' => (clone $tasks)->count('tasks.id'),
            'completed_tasks' => (clone $tasks)->where('tasks.status', TaskStatus::Done)->count('tasks.id'),
            'pending_tasks' => (clone $tasks)->whereNot('tasks.status', TaskStatus::Done)->count('tasks.id'),
            'overdue_tasks' => (clone $tasks)
                ->whereNot('tasks.status', TaskStatus::Done)
                ->whereDate('tasks.due_date', '<', now()->toDateString())
                ->count('tasks.id'),
        ];
    }
}
