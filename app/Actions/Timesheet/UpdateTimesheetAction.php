<?php

namespace App\Actions\Timesheet;

use App\Models\Timesheet;

class UpdateTimesheetAction
{
    public function handle(Timesheet $timesheet, array $data): Timesheet
    {
        $timesheet->update($data);

        return $timesheet->fresh(['user', 'project']);
    }
}
