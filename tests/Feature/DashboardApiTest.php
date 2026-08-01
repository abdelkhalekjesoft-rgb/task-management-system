<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_dashboard_statistics(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $activeProject = Project::factory()->create([
            'user_id' => $user->id,
            'status' => ProjectStatus::Active,
        ]);
        $completedProject = Project::factory()->create([
            'user_id' => $user->id,
            'status' => ProjectStatus::Completed,
        ]);
        Project::factory()->create([
            'user_id' => $otherUser->id,
            'status' => ProjectStatus::Active,
        ]);

        Task::factory()->create([
            'project_id' => $activeProject->id,
            'status' => TaskStatus::Done,
            'due_date' => now()->subDays(2)->toDateString(),
        ]);
        Task::factory()->create([
            'project_id' => $activeProject->id,
            'status' => TaskStatus::Todo,
            'due_date' => now()->subDay()->toDateString(),
        ]);
        Task::factory()->create([
            'project_id' => $completedProject->id,
            'status' => TaskStatus::InProgress,
            'due_date' => now()->addDay()->toDateString(),
        ]);
        Task::factory()->create([
            'project_id' => Project::factory()->create(['user_id' => $otherUser->id])->id,
            'status' => TaskStatus::Todo,
            'due_date' => now()->subDay()->toDateString(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Dashboard statistics retrieved successfully.')
            ->assertJsonPath('data.total_projects', 2)
            ->assertJsonPath('data.active_projects', 1)
            ->assertJsonPath('data.total_tasks', 3)
            ->assertJsonPath('data.completed_tasks', 1)
            ->assertJsonPath('data.pending_tasks', 2)
            ->assertJsonPath('data.overdue_tasks', 1);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->getJson('/api/dashboard')
            ->assertUnauthorized();
    }
}
