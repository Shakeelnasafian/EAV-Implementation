<?php

namespace App\Services;

use App\Models\Timesheet;
use Illuminate\Database\Eloquent\Collection;

class TimesheetService
{
    public function getAll(): Collection
    {
        return Timesheet::with(['user', 'project'])->get();
    }
}
