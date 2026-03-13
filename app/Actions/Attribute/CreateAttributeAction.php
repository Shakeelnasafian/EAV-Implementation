<?php

namespace App\Actions\Attribute;

use App\Models\Attribute;

class CreateAttributeAction
{
    /**
     * Create and persist a new Attribute model from the provided data.
     *
     * @param array $data The attribute properties used for creation; keys should match the model's fillable attributes.
     * @return Attribute The newly created Attribute model instance.
     */
    public function handle(array $data): Attribute
    {
        return Attribute::create($data);
    }
}
