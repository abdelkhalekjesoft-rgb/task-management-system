<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demoUser = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $apiProject = $this->syncDemoProject($demoUser, [
            'name' => 'API Development',
            'description' => 'Build and document the task management REST API.',
            'status' => ProjectStatus::Active,
        ]);
        $this->syncDemoTasks($apiProject, [
            [
                'title' => 'Create authentication endpoints',
                'description' => 'Implement register, login, and logout endpoints using Laravel Sanctum.',
                'priority' => TaskPriority::High,
                'status' => TaskStatus::Done,
                'due_date' => now()->subDays(4)->toDateString(),
            ],
            [
                'title' => 'Build project CRUD endpoints',
                'description' => 'Allow authenticated users to create, update, list, view, and delete their projects.',
                'priority' => TaskPriority::High,
                'status' => TaskStatus::Done,
                'due_date' => now()->subDays(2)->toDateString(),
            ],
            [
                'title' => 'Implement task filters',
                'description' => 'Support filtering tasks by status, priority, and title search.',
                'priority' => TaskPriority::High,
                'status' => TaskStatus::InProgress,
                'due_date' => now()->addDays(2)->toDateString(),
            ],
            [
                'title' => 'Add dashboard statistics',
                'description' => 'Return project and task summary counts for the authenticated user.',
                'priority' => TaskPriority::Medium,
                'status' => TaskStatus::Todo,
                'due_date' => now()->addDays(5)->toDateString(),
            ],
            [
                'title' => 'Review overdue task notification plan',
                'description' => 'Prepare a queue-based notification approach for overdue tasks.',
                'priority' => TaskPriority::High,
                'status' => TaskStatus::Todo,
                'due_date' => now()->subDay()->toDateString(),
            ],
        ]);

        $databaseProject = $this->syncDemoProject($demoUser, [
            'name' => 'Database Design',
            'description' => 'Design relationships, migrations, factories, and seed data.',
            'status' => ProjectStatus::Completed,
        ]);
        $this->syncDemoTasks($databaseProject, [
            [
                'title' => 'Create projects table migration',
                'description' => 'Add ownership, status, timestamps, and soft deletes for projects.',
                'priority' => TaskPriority::High,
                'status' => TaskStatus::Done,
                'due_date' => now()->subDays(8)->toDateString(),
            ],
            [
                'title' => 'Create tasks table migration',
                'description' => 'Add project relationship, priority, status, due date, and soft deletes for tasks.',
                'priority' => TaskPriority::High,
                'status' => TaskStatus::Done,
                'due_date' => now()->subDays(7)->toDateString(),
            ],
            [
                'title' => 'Add sample data factories',
                'description' => 'Create reusable factory states for projects and tasks.',
                'priority' => TaskPriority::Medium,
                'status' => TaskStatus::Done,
                'due_date' => now()->subDays(3)->toDateString(),
            ],
        ]);

        $archiveProject = $this->syncDemoProject($demoUser, [
            'name' => 'Legacy Planning',
            'description' => 'Archived sample project for filtering and dashboard checks.',
            'status' => ProjectStatus::Archived,
        ]);
        $this->syncDemoTasks($archiveProject, [
            [
                'title' => 'Review previous task workflow',
                'description' => 'Compare the old planning workflow with the new API structure.',
                'priority' => TaskPriority::Low,
                'status' => TaskStatus::Done,
                'due_date' => now()->subDays(15)->toDateString(),
            ],
            [
                'title' => 'Archive unused project notes',
                'description' => 'Keep old notes available without showing them as active work.',
                'priority' => TaskPriority::Low,
                'status' => TaskStatus::Done,
                'due_date' => now()->subDays(12)->toDateString(),
            ],
        ]);

        if (User::query()->where('email', '!=', 'test@example.com')->doesntExist()) {
            User::factory()
                ->count(4)
                ->has(
                    Project::factory()
                        ->count(3)
                        ->has(Task::factory()->count(5))
                )
                ->create();
        }
    }

    /**
     * @param  array{name: string, description: string, status: ProjectStatus}  $data
     */
    private function syncDemoProject(User $user, array $data): Project
    {
        return Project::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => $data['name'],
            ],
            [
                'description' => $data['description'],
                'status' => $data['status'],
            ]
        );
    }

    /**
     * @param  array<int, array{title: string, description: string, priority: TaskPriority, status: TaskStatus, due_date: string}>  $tasks
     */
    private function syncDemoTasks(Project $project, array $tasks): void
    {
        $project->tasks()->delete();

        foreach ($tasks as $task) {
            $project->tasks()->create($task);
        }
    }
}
