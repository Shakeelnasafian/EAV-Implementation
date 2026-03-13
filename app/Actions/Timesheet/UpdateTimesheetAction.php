<?php

namespace App\Actions\Timesheet;

use App\Models\Timesheet;

class UpdateTimesheetAction
{
    /**
     * Update the given timesheet with provided attributes and return the refreshed instance with `user` and `project` relations loaded.
     *
     * @param Timesheet $timesheet The Timesheet model to update.
     * @param array $data Associative array of attributes to apply to the timesheet.
     * @return Timesheet The updated Timesheet instance reloaded from the database with `user` and `project` relations.
     */
    public function handle(Timesheet $timesheet, array $data): Timesheet
    {
        $timesheet->update($data);

        return $timesheet->fresh(['user', 'project']);
    }
}
