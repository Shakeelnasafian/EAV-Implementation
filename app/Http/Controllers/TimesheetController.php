<?php

namespace App\Http\Controllers;

use App\Actions\Timesheet\CreateTimesheetAction;
use App\Actions\Timesheet\DeleteTimesheetAction;
use App\Actions\Timesheet\RestoreTimesheetAction;
use App\Actions\Timesheet\UpdateTimesheetAction;
use App\Http\Requests\Timesheet\IndexTimesheetRequest;
use App\Http\Requests\Timesheet\StoreTimesheetRequest;
use App\Http\Requests\Timesheet\UpdateTimesheetRequest;
use App\Http\Resources\TimesheetResource;
use App\Models\Timesheet;
use App\Services\TimesheetService;
use App\Support\ApiResponse;
use OpenApi\Attributes as OA;

class TimesheetController extends Controller
{
    public function __construct(private readonly TimesheetService $timesheetService) {}

    #[OA\Get(
        path: '/timesheets',
        summary: 'List timesheets (users see own, admins see all)',
        security: [['bearerAuth' => []]],
        tags: ['Timesheets'],
        parameters: [
            new OA\Parameter(name: 'page',       in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page',   in: 'query', schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'user_id',    in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'project_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date_from',  in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to',    in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated timesheet list')]
    )]
    public function index(IndexTimesheetRequest $request)
    {
        $this->authorize('viewAny', Timesheet::class);

        $timesheets = $this->timesheetService->getAll($request->validated(), $request->user());

        return ApiResponse::paginated(TimesheetResource::collection($timesheets));
    }

    #[OA\Post(
        path: '/timesheets',
        summary: 'Log a new timesheet entry',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['task_name', 'date', 'hours', 'user_id', 'project_id'],
            properties: [
                new OA\Property(property: 'task_name',  type: 'string'),
                new OA\Property(property: 'date',       type: 'string', format: 'date'),
                new OA\Property(property: 'hours',      type: 'number', format: 'float'),
                new OA\Property(property: 'user_id',    type: 'integer'),
                new OA\Property(property: 'project_id', type: 'integer'),
            ]
        )),
        tags: ['Timesheets'],
        responses: [
            new OA\Response(response: 201, description: 'Timesheet created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreTimesheetRequest $request, CreateTimesheetAction $action)
    {
        $this->authorize('create', Timesheet::class);

        $timesheet = $action->handle($request->validated());
        $timesheet->load(['user', 'project']);

        return ApiResponse::created(new TimesheetResource($timesheet), 'Timesheet created successfully');
    }

    #[OA\Get(
        path: '/timesheets/{id}',
        summary: 'Get a single timesheet',
        security: [['bearerAuth' => []]],
        tags: ['Timesheets'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Timesheet detail'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function show(Timesheet $timesheet)
    {
        $this->authorize('view', $timesheet);

        $timesheet->load(['user', 'project']);

        return ApiResponse::success(new TimesheetResource($timesheet));
    }

    #[OA\Put(
        path: '/timesheets/{id}',
        summary: 'Update a timesheet (own or admin/manager)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'task_name',  type: 'string'),
            new OA\Property(property: 'date',       type: 'string', format: 'date'),
            new OA\Property(property: 'hours',      type: 'number', format: 'float'),
            new OA\Property(property: 'user_id',    type: 'integer'),
            new OA\Property(property: 'project_id', type: 'integer'),
        ])),
        tags: ['Timesheets'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Timesheet updated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function update(UpdateTimesheetRequest $request, Timesheet $timesheet, UpdateTimesheetAction $action)
    {
        $this->authorize('update', $timesheet);

        $timesheet = $action->handle($timesheet, $request->validated());

        return ApiResponse::success(new TimesheetResource($timesheet), 'Timesheet updated successfully');
    }

    #[OA\Delete(
        path: '/timesheets/{id}',
        summary: 'Soft delete a timesheet',
        security: [['bearerAuth' => []]],
        tags: ['Timesheets'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Timesheet deleted'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(Timesheet $timesheet, DeleteTimesheetAction $action)
    {
        $this->authorize('delete', $timesheet);

        $action->handle($timesheet);

        return ApiResponse::success(null, 'Timesheet deleted successfully');
    }

    #[OA\Post(
        path: '/timesheets/{id}/restore',
        summary: 'Restore a soft-deleted timesheet (admin/manager)',
        security: [['bearerAuth' => []]],
        tags: ['Timesheets'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Timesheet restored')]
    )]
    public function restore(Timesheet $timesheet, RestoreTimesheetAction $action)
    {
        $this->authorize('restore', $timesheet);

        $timesheet = $action->handle($timesheet);

        return ApiResponse::success(new TimesheetResource($timesheet), 'Timesheet restored successfully');
    }
}
