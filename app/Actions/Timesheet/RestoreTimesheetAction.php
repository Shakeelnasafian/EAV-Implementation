<?php

namespace App\Actions\Timesheet;

use App\Models\Timesheet;

class RestoreTimesheetAction
{
    public function handle(Timesheet $timesheet): Timesheet
    {
        $timesheet->restore();

        return $timesheet->load(['user', 'project']);
    }
}
