<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_has_many_tasks(): void
    {
        $project = Project::factory()
            ->has(Task::factory()->count(3))
            ->create();

        $this->assertCount(3, $project->tasks);
        $this->assertInstanceOf(Task::class, $project->tasks->first());
    }

    public function test_task_belongs_to_project_and_casts_enums(): void
    {
        $task = Task::factory()->create([
            'priority' => TaskPriority::High,
            'status' => TaskStatus::InProgress,
        ]);

        $this->assertInstanceOf(Project::class, $task->project);
        $this->assertSame(TaskPriority::High, $task->priority);
        $this->assertSame(TaskStatus::InProgress, $task->status);
    }

    public function test_task_uses_soft_deletes(): void
    {
        $task = Task::factory()->create();

        $task->delete();

        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }
}
