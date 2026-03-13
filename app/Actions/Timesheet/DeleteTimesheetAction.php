<?php

namespace App\Actions\Timesheet;

use App\Models\Timesheet;

class DeleteTimesheetAction
{
    /**
     * Deletes the given timesheet record.
     *
     * @param Timesheet $timesheet The timesheet model instance to delete.
     */
    public function handle(Timesheet $timesheet): void
    {
        $timesheet->delete();
    }
}
