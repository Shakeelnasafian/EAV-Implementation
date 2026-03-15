<?php

namespace App\Actions\Attribute;

use App\Models\Attribute;

class UpdateAttributeAction
{
    /**
     * Update the given Attribute model with the provided data and return its refreshed instance.
     *
     * @param \App\Models\Attribute $attribute The Attribute model to update.
     * @param array $data The attributes to update on the model.
     * @return \App\Models\Attribute The updated Attribute model reloaded from the database.
     */
    public function handle(Attribute $attribute, array $data): Attribute
    {
        $attribute->update($data);

        return $attribute->fresh();
    }
}
