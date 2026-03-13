<?php

namespace App\Http\Controllers;

use App\Actions\Project\CreateProjectAction;
use App\Actions\Project\DeleteProjectAction;
use App\Actions\Project\UpdateProjectAction;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projectService) {}

    public function index()
    {
        $projects = $this->projectService->getAll();

        if ($projects->isEmpty()) {
            return ApiResponse::notFound('No projects found');
        }

        return ApiResponse::success(ProjectResource::collection($projects));
    }

    public function store(StoreProjectRequest $request, CreateProjectAction $action)
    {
        $project = $action->handle($request->validated());

        return ApiResponse::created(new ProjectResource($project), 'Project created successfully');
    }

    public function show(Project $project)
    {
        $project->load(['attributeValues.attribute', 'users']);

        return ApiResponse::success(new ProjectResource($project));
    }

    public function update(UpdateProjectRequest $request, Project $project, UpdateProjectAction $action)
    {
        $project = $action->handle($project, $request->validated());

        return ApiResponse::success(new ProjectResource($project), 'Project updated successfully');
    }

    public function destroy(Project $project, DeleteProjectAction $action)
    {
        if ($project->users()->exists()) {
            return ApiResponse::error('Cannot delete project with assigned users', 400);
        }

        $action->handle($project);

        return ApiResponse::success(null, 'Project deleted successfully');
    }

    public function filter(Request $request)
    {
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
        } catch (\Exception $e) {
            return ApiResponse::serverError();
        }
    }
}
