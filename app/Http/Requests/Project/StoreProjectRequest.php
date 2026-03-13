<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseFormRequest;

class StoreProjectRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'status'       => 'required|string|max:50',
            'users'        => 'nullable|array',
            'users.*'      => 'exists:users,id',
            'attributes'   => 'nullable|array',
            'attributes.*' => 'nullable|string|max:255',
        ];
    }
}
