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
    /**
 * Create a new TimesheetController instance and inject the TimesheetService.
 *
 * The injected service is used to retrieve and manage timesheet records for controller actions.
 */
public function __construct(private readonly TimesheetService $timesheetService) {}

    /**
     * Retrieve all timesheets and return them as a resource collection.
     *
     * @return \Illuminate\Http\JsonResponse A 200 success response containing a collection of `TimesheetResource` when timesheets exist, or a 404 not found response with the message "No timesheets found" when none are available.
     */
    public function index()
    {
        $timesheets = $this->timesheetService->getAll();

        if ($timesheets->isEmpty()) {
            return ApiResponse::notFound('No timesheets found');
        }

        return ApiResponse::success(TimesheetResource::collection($timesheets));
    }

    /**
     * Create a new timesheet from validated request data and return a created API response.
     *
     * Eager-loads the `user` and `project` relationships on the created timesheet before returning.
     *
     * @param StoreTimesheetRequest $request The incoming request with validated timesheet data.
     * @param CreateTimesheetAction $action Action that performs the creation of the timesheet.
     * @return \Illuminate\Http\JsonResponse The created response containing the TimesheetResource and the message "Timesheet created successfully".
     */
    public function store(StoreTimesheetRequest $request, CreateTimesheetAction $action)
    {
        $timesheet = $action->handle($request->validated());
        $timesheet->load(['user', 'project']);

        return ApiResponse::created(new TimesheetResource($timesheet), 'Timesheet created successfully');
    }

    /**
     * Return a single timesheet resource with its `user` and `project` relationships loaded.
     *
     * @param Timesheet $timesheet The timesheet model instance resolved via route model binding.
     * @return \Illuminate\Http\JsonResponse An ApiResponse containing the TimesheetResource for the given timesheet.
     */
    public function show(Timesheet $timesheet)
    {
        $timesheet->load(['user', 'project']);

        return ApiResponse::success(new TimesheetResource($timesheet));
    }

    /**
     * Update an existing timesheet with validated request data.
     *
     * @param UpdateTimesheetRequest $request Validated input for updating the timesheet.
     * @param Timesheet $timesheet The timesheet model to be updated.
     * @param UpdateTimesheetAction $action Action that applies the update to the timesheet.
     * @return \Illuminate\Http\JsonResponse A success ApiResponse containing the updated TimesheetResource and the message "Timesheet updated successfully".
     */
    public function update(UpdateTimesheetRequest $request, Timesheet $timesheet, UpdateTimesheetAction $action)
    {
        $timesheet = $action->handle($timesheet, $request->validated());

        return ApiResponse::success(new TimesheetResource($timesheet), 'Timesheet updated successfully');
    }

    /**
     * Delete the given timesheet and return a success response.
     *
     * @param Timesheet $timesheet The timesheet instance obtained via route model binding.
     * @return \Illuminate\Http\JsonResponse A success response with `null` data and the message "Timesheet deleted successfully".
     */
    public function destroy(Timesheet $timesheet, DeleteTimesheetAction $action)
    {
        $action->handle($timesheet);

        return ApiResponse::success(null, 'Timesheet deleted successfully');
    }
}
