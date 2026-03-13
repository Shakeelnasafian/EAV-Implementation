<?php

namespace App\Actions\Timesheet;

use App\Models\Timesheet;

class DeleteTimesheetAction
{
    public function handle(Timesheet $timesheet): void
    {
        $timesheet->delete();
    }
}
