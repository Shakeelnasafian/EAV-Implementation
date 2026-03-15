<?php

namespace App\Http\Requests\Attribute;

use App\Http\Requests\BaseFormRequest;

class StoreAttributeRequest extends BaseFormRequest
{
    /**
     * Validation rules for storing an attribute.
     *
     * - `name`: required string, unique in `attributes.name`, maximum length 255.
     * - `type`: required string, allowed values are `text`, `date`, `number`, and `select`.
     *
     * @return array<string,string> Mapping of request field names to their validation rule strings.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:attributes,name|max:255',
            'type' => 'required|string|in:text,date,number,select',
        ];
    }
}
