<?php

namespace App\Http\Requests\Timesheet;

use App\Http\Requests\BaseFormRequest;

class StoreTimesheetRequest extends BaseFormRequest
{
    /**
     * Validation rules for creating a timesheet.
     *
     * Defines required fields and their constraints:
     * - task_name: required string, maximum 255 characters.
     * - date: required valid date.
     * - hours: required numeric value, minimum 0.1.
     * - user_id: required and must exist in users.id.
     * - project_id: required and must exist in projects.id.
     *
     * @return array<string,string> Mapping of field names to validation rule strings.
     */
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
