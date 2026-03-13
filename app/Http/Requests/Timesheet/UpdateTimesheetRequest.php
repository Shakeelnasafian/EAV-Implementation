<?php

namespace App\Http\Requests\Timesheet;

use App\Http\Requests\BaseFormRequest;

class UpdateTimesheetRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'task_name'  => 'sometimes|string|max:255',
            'date'       => 'sometimes|date',
            'hours'      => 'sometimes|numeric|min:0.1',
            'user_id'    => 'sometimes|exists:users,id',
            'project_id' => 'sometimes|exists:projects,id',
        ];
    }
}
