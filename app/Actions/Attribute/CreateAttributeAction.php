<?php

namespace App\Actions\Attribute;

use App\Models\Attribute;

class CreateAttributeAction
{
    public function handle(array $data): Attribute
    {
        return Attribute::create($data);
    }
}
