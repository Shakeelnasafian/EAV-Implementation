<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    /**
     * Retrieve all Project records with the `attributeValues.attribute` and `users` relationships eager-loaded.
     *
     * @return \Illuminate\Support\Collection Collection of Project models with `attributeValues.attribute` and `users` relations loaded.
     */
    public function getAll(): Collection
    {
        return Project::with(['attributeValues.attribute', 'users'])->get();
    }

    /**
     * Filter projects by attribute name/value pairs and return the matching projects with related data.
     *
     * @param array $filters Associative array of attribute filters where keys are attribute names and values are filter values. Numeric values are matched exactly, values parseable as dates are matched by date, and other values are matched partially (SQL LIKE).
     * @return \Illuminate\Support\Collection A collection of Project models with the `attributeValues.attribute` and `users` relationships eager-loaded.
     */
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
