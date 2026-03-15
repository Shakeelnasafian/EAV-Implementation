<?php

namespace App\Actions\Attribute;

use App\Models\Attribute;

class DeleteAttributeAction
{
    /**
     * Deletes the given Attribute model.
     *
     * @param \App\Models\Attribute $attribute The attribute model to delete.
     */
    public function handle(Attribute $attribute): void
    {
        $attribute->delete();
    }
}
