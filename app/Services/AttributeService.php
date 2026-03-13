<?php

namespace App\Services;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Collection;

class AttributeService
{
    public function getAll(): Collection
    {
        return Attribute::all();
    }

    public function findById(int $id): Attribute
    {
        return Attribute::findOrFail($id);
    }
}
