<?php

namespace App\Http\Requests\Attribute;

use App\Http\Requests\BaseFormRequest;

class UpdateAttributeRequest extends BaseFormRequest
{
    /**
     * Validation rules for updating an attribute.
     *
     * The returned rules make `name` optional, require it to be a string unique among attributes (excluding the current attribute) with a maximum length of 255, and make `type` optional, require it to be a string with one of: `text`, `date`, `number`, `select`.
     *
     * @return array<string,string> Associative array mapping request field names to validation rule strings.
     */
    public function rules(): array
    {
        $attributeId = $this->route('attribute');

        return [
            'name' => 'sometimes|string|unique:attributes,name,' . $attributeId . '|max:255',
            'type' => 'sometimes|string|in:text,date,number,select',
        ];
    }
}
