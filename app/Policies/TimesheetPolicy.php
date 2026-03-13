<?php

namespace App\Policies;

use App\Models\Timesheet;
use App\Models\User;

class TimesheetPolicy
{
    public function viewAny(User $user): bool
    {
        // Admins and managers see all; regular users only see their own (filtered in service)
        return true;
    }

    public function view(User $user, Timesheet $timesheet): bool
    {
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }

        return $timesheet->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Timesheet $timesheet): bool
    {
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }

        return $timesheet->user_id === $user->id;
    }

    public function delete(User $user, Timesheet $timesheet): bool
    {
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }

        return $timesheet->user_id === $user->id;
    }

    public function restore(User $user, Timesheet $timesheet): bool
    {
        return in_array($user->role, ['admin', 'manager']);
    }
}
