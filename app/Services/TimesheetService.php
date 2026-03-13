<?php

namespace App\Services;

use App\Models\Timesheet;
use Illuminate\Database\Eloquent\Collection;

class TimesheetService
{
    /**
     * Retrieve all timesheet records with their associated user and project relationships.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Timesheet> Collection of Timesheet models with `user` and `project` relations loaded.
     */
    public function getAll(): Collection
    {
        return Timesheet::with(['user', 'project'])->get();
    }
}
