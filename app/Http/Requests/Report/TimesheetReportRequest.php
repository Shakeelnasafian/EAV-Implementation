<?php

namespace App\Http\Requests\Report;

use App\Http\Requests\BaseFormRequest;

class TimesheetReportRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'user_id'    => 'sometimes|integer|exists:users,id',
            'project_id' => 'sometimes|integer|exists:projects,id',
            'date_from'  => 'sometimes|date',
            'date_to'    => 'sometimes|date|after_or_equal:date_from',
        ];
    }
}
