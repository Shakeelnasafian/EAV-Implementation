<?php

namespace App\Actions\Timesheet;

use App\Models\Timesheet;

class CreateTimesheetAction
{
    public function handle(array $data): Timesheet
    {
        return Timesheet::create($data);
    }
}
