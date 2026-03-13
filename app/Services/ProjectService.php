<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Project::with(['attributeValues.attribute', 'users']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'LIKE', '%' . $filters['search'] . '%');
        }

        $sort  = in_array($filters['sort'] ?? '', ['id', 'name', 'status', 'created_at']) ? $filters['sort'] : 'created_at';
        $order = ($filters['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sort, $order);

        return $query->paginate($filters['per_page'] ?? 15);
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
