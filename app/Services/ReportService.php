<?php

namespace App\Services;

use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function timesheetSummary(array $filters = [], ?User $authUser = null): array
    {
        $query = Timesheet::query();

        // Regular users can only see their own data
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

        $totals = (clone $query)->selectRaw('COUNT(*) as total_entries, SUM(hours) as total_hours')->first();

        $byProject = (clone $query)
            ->select('project_id', DB::raw('SUM(hours) as total_hours'), DB::raw('COUNT(*) as entries'))
            ->with('project:id,name')
            ->groupBy('project_id')
            ->get()
            ->map(fn ($row) => [
                'project_id'   => $row->project_id,
                'project_name' => $row->project?->name,
                'total_hours'  => round((float) $row->total_hours, 2),
                'entries'      => $row->entries,
            ]);

        $byUser = (clone $query)
            ->select('user_id', DB::raw('SUM(hours) as total_hours'), DB::raw('COUNT(*) as entries'))
            ->with('user:id,first_name,last_name,email')
            ->groupBy('user_id')
            ->get()
            ->map(fn ($row) => [
                'user_id'     => $row->user_id,
                'user_name'   => $row->user ? $row->user->first_name . ' ' . $row->user->last_name : null,
                'total_hours' => round((float) $row->total_hours, 2),
                'entries'     => $row->entries,
            ]);

        $byDay = (clone $query)
            ->selectRaw('DATE(date) as day, SUM(hours) as total_hours, COUNT(*) as entries')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($row) => [
                'day'         => $row->day,
                'total_hours' => round((float) $row->total_hours, 2),
                'entries'     => $row->entries,
            ]);

        return [
            'summary'    => [
                'total_entries' => (int) ($totals->total_entries ?? 0),
                'total_hours'   => round((float) ($totals->total_hours ?? 0), 2),
            ],
            'by_project' => $byProject,
            'by_user'    => $byUser,
            'by_day'     => $byDay,
            'filters'    => array_filter($filters),
        ];
    }
}
