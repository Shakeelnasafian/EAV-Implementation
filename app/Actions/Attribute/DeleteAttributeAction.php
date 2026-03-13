<?php

namespace App\Actions\Attribute;

use App\Models\Attribute;

class DeleteAttributeAction
{
    public function handle(Attribute $attribute): void
    {
        $attribute->delete();
    }
}
