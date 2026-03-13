<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseFormRequest;

class IndexProjectRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'page'     => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort'     => 'sometimes|string|in:id,name,status,created_at',
            'order'    => 'sometimes|string|in:asc,desc',
            'status'   => 'sometimes|string|max:50',
            'search'   => 'sometimes|string|max:255',
        ];
    }
}
