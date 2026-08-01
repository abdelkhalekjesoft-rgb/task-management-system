<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_projects(): void
    {
        $user = User::factory()
            ->has(Project::factory()->count(2))
            ->create();

        $this->assertCount(2, $user->projects);
        $this->assertInstanceOf(Project::class, $user->projects->first());
    }

    public function test_project_belongs_to_user_and_casts_status(): void
    {
        $project = Project::factory()->create([
            'status' => ProjectStatus::Completed,
        ]);

        $this->assertInstanceOf(User::class, $project->user);
        $this->assertSame(ProjectStatus::Completed, $project->status);
    }

    public function test_project_uses_soft_deletes(): void
    {
        $project = Project::factory()->create();

        $project->delete();

        $this->assertSoftDeleted('projects', [
            'id' => $project->id,
        ]);
    }
}
