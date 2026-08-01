<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_project(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/projects', [
            'name' => 'Website Redesign',
            'description' => 'Refresh the marketing website.',
            'status' => ProjectStatus::Active->value,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Project created successfully.')
            ->assertJsonPath('data.name', 'Website Redesign')
            ->assertJsonPath('data.status', ProjectStatus::Active->value);

        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'name' => 'Website Redesign',
        ]);
    }

    public function test_user_can_list_only_their_projects(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Project::factory()->count(2)->create(['user_id' => $user->id]);
        Project::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/projects');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Projects retrieved successfully.')
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'status', 'created_at', 'updated_at'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_user_can_view_their_project(): void
    {
        $project = Project::factory()->create();

        Sanctum::actingAs($project->user);

        $response = $this->getJson("/api/projects/{$project->id}");

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Project retrieved successfully.')
            ->assertJsonPath('data.id', $project->id)
            ->assertJsonPath('data.name', $project->name);
    }

    public function test_user_cannot_view_another_users_project(): void
    {
        $project = Project::factory()->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/projects/{$project->id}")
            ->assertForbidden();
    }

    public function test_user_can_update_their_project(): void
    {
        $project = Project::factory()->create([
            'status' => ProjectStatus::Active,
        ]);

        Sanctum::actingAs($project->user);

        $response = $this->putJson("/api/projects/{$project->id}", [
            'name' => 'Updated Project',
            'status' => ProjectStatus::Completed->value,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Project updated successfully.')
            ->assertJsonPath('data.name', 'Updated Project')
            ->assertJsonPath('data.status', ProjectStatus::Completed->value);
    }

    public function test_user_cannot_update_another_users_project(): void
    {
        $project = Project::factory()->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->putJson("/api/projects/{$project->id}", [
            'name' => 'Not Allowed',
        ])->assertForbidden();
    }

    public function test_user_can_delete_their_project(): void
    {
        $project = Project::factory()->create();

        Sanctum::actingAs($project->user);

        $this->deleteJson("/api/projects/{$project->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Project deleted successfully.')
            ->assertJsonPath('data', null);

        $this->assertSoftDeleted('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_project_requires_valid_data(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/projects', [
            'name' => '',
            'status' => 'paused',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'status']);
    }

    public function test_projects_require_authentication(): void
    {
        $this->getJson('/api/projects')
            ->assertUnauthorized();
    }
}
