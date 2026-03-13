<?php

namespace App\Http\Requests\Attribute;

use App\Http\Requests\BaseFormRequest;

class UpdateAttributeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $attributeId = $this->route('attribute');

        return [
            'name' => 'sometimes|string|unique:attributes,name,' . $attributeId . '|max:255',
            'type' => 'sometimes|string|in:text,date,number,select',
        ];
    }
}
