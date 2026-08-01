<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_sample_users_projects_and_tasks(): void
    {
        $this->seed(DatabaseSeeder::class);

        $demoUser = User::query()->where('email', 'test@example.com')->first();

        $this->assertNotNull($demoUser);
        $this->assertGreaterThanOrEqual(5, User::query()->count());
        $this->assertGreaterThanOrEqual(15, Project::query()->count());
        $this->assertGreaterThanOrEqual(67, Task::query()->count());
        $this->assertSame(3, $demoUser->projects()->count());
        $this->assertSame(10, Task::query()->whereHas('project', fn ($query) => $query->whereBelongsTo($demoUser))->count());
    }
}
