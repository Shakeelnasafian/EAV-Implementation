<?php

namespace App\Http\Controllers;

use App\Actions\Timesheet\CreateTimesheetAction;
use App\Actions\Timesheet\DeleteTimesheetAction;
use App\Actions\Timesheet\UpdateTimesheetAction;
use App\Http\Requests\Timesheet\StoreTimesheetRequest;
use App\Http\Requests\Timesheet\UpdateTimesheetRequest;
use App\Http\Resources\TimesheetResource;
use App\Models\Timesheet;
use App\Services\TimesheetService;
use App\Support\ApiResponse;

class TimesheetController extends Controller
{
    public function __construct(private readonly TimesheetService $timesheetService) {}

    public function index()
    {
        $timesheets = $this->timesheetService->getAll();

        if ($timesheets->isEmpty()) {
            return ApiResponse::notFound('No timesheets found');
        }

        return ApiResponse::success(TimesheetResource::collection($timesheets));
    }

    public function store(StoreTimesheetRequest $request, CreateTimesheetAction $action)
    {
        $timesheet = $action->handle($request->validated());
        $timesheet->load(['user', 'project']);

        return ApiResponse::created(new TimesheetResource($timesheet), 'Timesheet created successfully');
    }

    public function show(Timesheet $timesheet)
    {
        $timesheet->load(['user', 'project']);

        return ApiResponse::success(new TimesheetResource($timesheet));
    }

    public function update(UpdateTimesheetRequest $request, Timesheet $timesheet, UpdateTimesheetAction $action)
    {
        $timesheet = $action->handle($timesheet, $request->validated());

        return ApiResponse::success(new TimesheetResource($timesheet), 'Timesheet updated successfully');
    }

    public function destroy(Timesheet $timesheet, DeleteTimesheetAction $action)
    {
        $action->handle($timesheet);

        return ApiResponse::success(null, 'Timesheet deleted successfully');
    }
}
