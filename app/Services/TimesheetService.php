<?php

namespace App\Services;

use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TimesheetService
{
    public function getAll(array $filters = [], ?User $authUser = null): LengthAwarePaginator
    {
        $query = Timesheet::with(['user', 'project']);

        // Regular users can only see their own timesheets
        if ($authUser && $authUser->role === 'user') {
            $query->where('user_id', $authUser->id);
        } elseif (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        $sort  = in_array($filters['sort'] ?? '', ['id', 'task_name', 'date', 'hours', 'created_at']) ? $filters['sort'] : 'created_at';
        $order = ($filters['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sort, $order);

        return $query->paginate($filters['per_page'] ?? 15);
    }
}
