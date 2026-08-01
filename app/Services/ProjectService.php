<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function paginateForUser(User $user): LengthAwarePaginator
    {
        return $user->projects()
            ->latest()
            ->paginate(15);
    }

    /**
     * @param  array{name: string, description?: string|null, status?: ProjectStatus}  $data
     */
    public function createForUser(User $user, array $data): Project
    {
        return $user->projects()->create($data);
    }

    /**
     * @param  array{name?: string, description?: string|null, status?: ProjectStatus}  $data
     */
    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->refresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
