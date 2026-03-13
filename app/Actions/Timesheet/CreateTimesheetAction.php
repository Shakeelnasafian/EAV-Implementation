<?php

namespace App\Actions\Timesheet;

use App\Models\Timesheet;

class CreateTimesheetAction
{
    /**
     * Create a new timesheet record from the given attributes.
     *
     * @param array $data Associative array of Timesheet attributes to persist.
     * @return Timesheet The newly created Timesheet model instance.
     */
    public function handle(array $data): Timesheet
    {
        return Timesheet::create($data);
    }
}
