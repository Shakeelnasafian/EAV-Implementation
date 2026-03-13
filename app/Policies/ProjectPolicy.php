<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        // Admins and managers can view any project; users can only view their assigned projects
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }

        return $project->users()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager']);
    }

    public function update(User $user, Project $project): bool
    {
        return in_array($user->role, ['admin', 'manager']);
    }

    public function delete(User $user, Project $project): bool
    {
        return in_array($user->role, ['admin', 'manager']);
    }

    public function restore(User $user, Project $project): bool
    {
        return in_array($user->role, ['admin', 'manager']);
    }
}
