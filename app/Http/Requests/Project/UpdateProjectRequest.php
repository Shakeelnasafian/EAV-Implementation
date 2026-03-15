<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseFormRequest;

class UpdateProjectRequest extends BaseFormRequest
{
    /**
     * Get the validation rules for updating a project.
     *
     * @return array<string,string> Associative array mapping request field names to their validation rules.
     */
    public function rules(): array
    {
        return [
            'name'         => 'sometimes|string|max:255',
            'status'       => 'sometimes|string|max:50',
            'users'        => 'sometimes|array',
            'users.*'      => 'exists:users,id',
            'attributes'   => 'sometimes|array',
            'attributes.*' => 'nullable|string|max:255',
        ];
    }
}
