<?php

namespace App\Http\Controllers;

use App\Http\Requests\Report\TimesheetReportRequest;
use App\Services\ReportService;
use App\Support\ApiResponse;
use OpenApi\Attributes as OA;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    #[OA\Get(
        path: '/reports/timesheets',
        summary: 'Timesheet summary report',
        description: 'Returns total hours, breakdown by project, by user, and by day. Regular users only see their own data.',
        security: [['bearerAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'user_id',    in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'project_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date_from',  in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to',    in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Report data with summary, by_project, by_user, by_day breakdown'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function timesheets(TimesheetReportRequest $request)
    {
        $report = $this->reportService->timesheetSummary(
            $request->validated(),
            $request->user()
        );

        return ApiResponse::success($report, 'Timesheet report generated');
    }
}
