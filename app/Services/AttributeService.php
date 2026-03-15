<?php

namespace App\Services;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Collection;

class AttributeService
{
    /**
     * Retrieve all Attribute records.
     *
     * @return Collection|Attribute[] A collection of Attribute models.
     */
    public function getAll(): Collection
    {
        return Attribute::all();
    }

    /**
     * Retrieve an Attribute by its primary key.
     *
     * @param int $id The primary key of the Attribute to retrieve.
     * @return Attribute The found Attribute model.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If no Attribute with the given id exists.
     */
    public function findById(int $id): Attribute
    {
        return Attribute::findOrFail($id);
    }
}
