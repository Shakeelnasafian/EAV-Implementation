<?php

namespace App\Http\Requests\Timesheet;

use App\Http\Requests\BaseFormRequest;

class StoreTimesheetRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'task_name'  => 'required|string|max:255',
            'date'       => 'required|date',
            'hours'      => 'required|numeric|min:0.1',
            'user_id'    => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
        ];
    }
}
