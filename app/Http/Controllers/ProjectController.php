<?php

namespace App\Http\Controllers;

use App\Actions\Project\CreateProjectAction;
use App\Actions\Project\DeleteProjectAction;
use App\Actions\Project\RestoreProjectAction;
use App\Actions\Project\UpdateProjectAction;
use App\Http\Requests\Project\IndexProjectRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projectService) {}

    #[OA\Get(
        path: '/projects',
        summary: 'List all projects (paginated)',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        parameters: [
            new OA\Parameter(name: 'page',     in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'sort',     in: 'query', schema: new OA\Schema(type: 'string', enum: ['id', 'name', 'status', 'created_at'])),
            new OA\Parameter(name: 'order',    in: 'query', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'status',   in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'search',   in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated project list')]
    )]
    public function index(IndexProjectRequest $request)
    {
        $this->authorize('viewAny', Project::class);

        $projects = $this->projectService->getAll($request->validated());

        return ApiResponse::paginated(ProjectResource::collection($projects));
    }

    #[OA\Post(
        path: '/projects',
        summary: 'Create a new project (manager/admin)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['name', 'status'],
            properties: [
                new OA\Property(property: 'name',       type: 'string'),
                new OA\Property(property: 'status',     type: 'string'),
                new OA\Property(property: 'users',      type: 'array', items: new OA\Items(type: 'integer')),
                new OA\Property(property: 'attributes', type: 'object', example: ['department' => 'Engineering']),
            ]
        )),
        tags: ['Projects'],
        responses: [
            new OA\Response(response: 201, description: 'Project created'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(StoreProjectRequest $request, CreateProjectAction $action)
    {
        $this->authorize('create', Project::class);

        $project = $action->handle($request->validated());

        return ApiResponse::created(new ProjectResource($project), 'Project created successfully');
    }

    #[OA\Get(
        path: '/projects/{id}',
        summary: 'Get a single project with EAV attributes',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Project detail'),
            new OA\Response(response: 403, description: 'Forbidden — not assigned to this project'),
        ]
    )]
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['attributeValues.attribute', 'users']);

        return ApiResponse::success(new ProjectResource($project));
    }

    #[OA\Put(
        path: '/projects/{id}',
        summary: 'Update a project (manager/admin)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name',       type: 'string'),
            new OA\Property(property: 'status',     type: 'string'),
            new OA\Property(property: 'users',      type: 'array', items: new OA\Items(type: 'integer')),
            new OA\Property(property: 'attributes', type: 'object'),
        ])),
        tags: ['Projects'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Project updated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function update(UpdateProjectRequest $request, Project $project, UpdateProjectAction $action)
    {
        $this->authorize('update', $project);

        $project = $action->handle($project, $request->validated());

        return ApiResponse::success(new ProjectResource($project), 'Project updated successfully');
    }

    #[OA\Delete(
        path: '/projects/{id}',
        summary: 'Soft delete a project (manager/admin)',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Project deleted'),
            new OA\Response(response: 400, description: 'Cannot delete — has assigned users'),
        ]
    )]
    public function destroy(Project $project, DeleteProjectAction $action)
    {
        $this->authorize('delete', $project);

        if ($project->users()->exists()) {
            return ApiResponse::error('Cannot delete project with assigned users', 400);
        }

        $action->handle($project);

        return ApiResponse::success(null, 'Project deleted successfully');
    }

    #[OA\Post(
        path: '/projects/{id}/restore',
        summary: 'Restore a soft-deleted project (manager/admin)',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Project restored')]
    )]
    public function restore(Project $project, RestoreProjectAction $action)
    {
        $this->authorize('restore', $project);

        $project = $action->handle($project);

        return ApiResponse::success(new ProjectResource($project), 'Project restored successfully');
    }

    #[OA\Get(
        path: '/projects/filter',
        summary: 'Filter projects by dynamic EAV attribute values',
        description: 'Pass any EAV attribute name as a query param, e.g. ?department=Engineering&budget=50000',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        responses: [
            new OA\Response(response: 200, description: 'Filtered projects'),
            new OA\Response(response: 404, description: 'No projects matched'),
        ]
    )]
    public function filter(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        try {
            $filters = $request->all();
            $rules   = [];

            foreach ($filters as $key => $value) {
                if (is_numeric($value)) {
                    $rules[$key] = 'numeric';
                } elseif (strtotime($value) !== false) {
                    $rules[$key] = 'date';
                } else {
                    $rules[$key] = 'string';
                }
            }

            $validated = $request->validate($rules);
            $projects  = $this->projectService->filter($validated);

            if ($projects->isEmpty()) {
                return ApiResponse::notFound('No projects found matching the given criteria');
            }

            return ApiResponse::success(ProjectResource::collection($projects));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors(), 'Invalid filter parameters');
        }
    }
}
