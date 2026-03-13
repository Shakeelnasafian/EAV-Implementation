<?php

namespace App\Http\Requests\Timesheet;

use App\Http\Requests\BaseFormRequest;

class IndexTimesheetRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'page'       => 'sometimes|integer|min:1',
            'per_page'   => 'sometimes|integer|min:1|max:100',
            'sort'       => 'sometimes|string|in:id,task_name,date,hours,created_at',
            'order'      => 'sometimes|string|in:asc,desc',
            'user_id'    => 'sometimes|integer|exists:users,id',
            'project_id' => 'sometimes|integer|exists:projects,id',
            'date_from'  => 'sometimes|date',
            'date_to'    => 'sometimes|date|after_or_equal:date_from',
        ];
    }
}
