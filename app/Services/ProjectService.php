<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    public function getAll(): Collection
    {
        return Project::with(['attributeValues.attribute', 'users'])->get();
    }

    public function filter(array $filters): Collection
    {
        $query = Project::query();

        foreach ($filters as $attrName => $attrValue) {
            $query->whereHas('attributeValues.attribute', function ($q) use ($attrName) {
                $q->where('name', $attrName);
            })->whereHas('attributeValues', function ($q) use ($attrValue) {
                if (is_numeric($attrValue)) {
                    $q->where('value', '=', $attrValue);
                } elseif (strtotime($attrValue) !== false) {
                    $q->whereDate('value', '=', $attrValue);
                } else {
                    $q->where('value', 'LIKE', "%{$attrValue}%");
                }
            });
        }

        return $query->with(['attributeValues.attribute', 'users'])->get();
    }
}
