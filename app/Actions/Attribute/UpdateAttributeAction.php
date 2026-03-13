<?php

namespace App\Actions\Attribute;

use App\Models\Attribute;

class UpdateAttributeAction
{
    public function handle(Attribute $attribute, array $data): Attribute
    {
        $attribute->update($data);

        return $attribute->fresh();
    }
}
