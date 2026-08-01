<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_task_for_their_project(): void
    {
        $project = Project::factory()->create();

        Sanctum::actingAs($project->user);

        $response = $this->postJson("/api/projects/{$project->id}/tasks", [
            'title' => 'Prepare API documentation',
            'description' => 'Document all project endpoints.',
            'priority' => TaskPriority::High->value,
            'status' => TaskStatus::Todo->value,
            'due_date' => now()->addWeek()->toDateString(),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Task created successfully.')
            ->assertJsonPath('data.title', 'Prepare API documentation')
            ->assertJsonPath('data.priority', TaskPriority::High->value);

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title' => 'Prepare API documentation',
        ]);
    }

    public function test_user_cannot_create_task_for_another_users_project(): void
    {
        $project = Project::factory()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/projects/{$project->id}/tasks", [
            'title' => 'Not allowed',
        ])->assertForbidden();
    }

    public function test_user_can_list_only_tasks_for_their_project(): void
    {
        $project = Project::factory()->create();
        $otherProject = Project::factory()->create();

        Task::factory()->count(2)->create(['project_id' => $project->id]);
        Task::factory()->create(['project_id' => $otherProject->id]);

        Sanctum::actingAs($project->user);

        $response = $this->getJson("/api/projects/{$project->id}/tasks");

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Tasks retrieved successfully.')
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'project_id', 'title', 'description', 'priority', 'status', 'due_date', 'created_at', 'updated_at'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_user_can_filter_tasks_by_status_priority_and_title(): void
    {
        $project = Project::factory()->create();

        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Write API documentation',
            'priority' => TaskPriority::High,
            'status' => TaskStatus::Todo,
        ]);
        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Review frontend task',
            'priority' => TaskPriority::High,
            'status' => TaskStatus::Done,
        ]);
        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Write seeders',
            'priority' => TaskPriority::Low,
            'status' => TaskStatus::Todo,
        ]);

        Sanctum::actingAs($project->user);

        $response = $this->getJson(
            "/api/projects/{$project->id}/tasks?status=todo&priority=high&search=api"
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Write API documentation');
    }

    public function test_user_can_filter_tasks_by_status_only(): void
    {
        $project = Project::factory()->create();

        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Prepare sprint plan',
            'status' => TaskStatus::Todo,
        ]);
        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Deploy completed release',
            'status' => TaskStatus::Done,
        ]);

        Sanctum::actingAs($project->user);

        $response = $this->getJson("/api/projects/{$project->id}/tasks?status=todo");

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Prepare sprint plan')
            ->assertJsonPath('data.0.status', TaskStatus::Todo->value);
    }

    public function test_user_can_filter_tasks_by_priority_only(): void
    {
        $project = Project::factory()->create();

        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Fix production issue',
            'priority' => TaskPriority::High,
        ]);
        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Refine backlog labels',
            'priority' => TaskPriority::Low,
        ]);

        Sanctum::actingAs($project->user);

        $response = $this->getJson("/api/projects/{$project->id}/tasks?priority=high");

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Fix production issue')
            ->assertJsonPath('data.0.priority', TaskPriority::High->value);
    }

    public function test_task_search_is_scoped_to_the_requested_project(): void
    {
        $project = Project::factory()->create();
        $otherProject = Project::factory()->create();

        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Write API documentation',
        ]);
        Task::factory()->create([
            'project_id' => $otherProject->id,
            'title' => 'Write API documentation for another project',
        ]);

        Sanctum::actingAs($project->user);

        $response = $this->getJson("/api/projects/{$project->id}/tasks?search=documentation");

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.project_id', $project->id)
            ->assertJsonPath('data.0.title', 'Write API documentation');
    }

    public function test_task_filters_require_valid_values(): void
    {
        $project = Project::factory()->create();

        Sanctum::actingAs($project->user);

        $response = $this->getJson("/api/projects/{$project->id}/tasks?status=blocked&priority=urgent");

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status', 'priority']);
    }

    public function test_user_cannot_list_tasks_for_another_users_project(): void
    {
        $project = Project::factory()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/projects/{$project->id}/tasks")
            ->assertForbidden();
    }

    public function test_user_can_update_their_task(): void
    {
        $task = Task::factory()->create([
            'status' => TaskStatus::Todo,
        ]);

        Sanctum::actingAs($task->project->user);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated task title',
            'status' => TaskStatus::InProgress->value,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Task updated successfully.')
            ->assertJsonPath('data.title', 'Updated task title')
            ->assertJsonPath('data.status', TaskStatus::InProgress->value);
    }

    public function test_user_cannot_update_another_users_task(): void
    {
        $task = Task::factory()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Not allowed',
        ])->assertForbidden();
    }

    public function test_user_can_delete_their_task(): void
    {
        $task = Task::factory()->create();

        Sanctum::actingAs($task->project->user);

        $this->deleteJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Task deleted successfully.')
            ->assertJsonPath('data', null);

        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_task_requires_valid_data(): void
    {
        $project = Project::factory()->create();

        Sanctum::actingAs($project->user);

        $response = $this->postJson("/api/projects/{$project->id}/tasks", [
            'title' => '',
            'priority' => 'urgent',
            'status' => 'blocked',
            'due_date' => 'not-a-date',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'priority', 'status', 'due_date']);
    }

    public function test_task_routes_require_authentication(): void
    {
        $project = Project::factory()->create();

        $this->getJson("/api/projects/{$project->id}/tasks")
            ->assertUnauthorized();
    }
}
