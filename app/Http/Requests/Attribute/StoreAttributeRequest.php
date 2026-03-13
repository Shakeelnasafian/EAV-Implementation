<?php

namespace App\Http\Requests\Attribute;

use App\Http\Requests\BaseFormRequest;

class StoreAttributeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:attributes,name|max:255',
            'type' => 'required|string|in:text,date,number,select',
        ];
    }
}
