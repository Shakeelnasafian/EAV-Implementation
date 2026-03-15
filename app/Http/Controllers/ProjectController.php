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
    /**
 * Create a new ProjectController instance and inject the ProjectService.
 *
 * @param \App\Services\ProjectService $projectService Service responsible for project-related operations.
 */
public function __construct(private readonly ProjectService $projectService) {}

    /**
     * Retrieve all projects and return them as a collection resource.
     *
     * If no projects are found, returns a not-found ApiResponse with message "No projects found".
     *
     * @return \App\Support\ApiResponse An ApiResponse containing a collection of ProjectResource on success, or a not-found response with the message 'No projects found'.
     */
    public function index()
    {
        $projects = $this->projectService->getAll();

        if ($projects->isEmpty()) {
            return ApiResponse::notFound('No projects found');
        }

        return ApiResponse::success(ProjectResource::collection($projects));
    }

    /**
     * Creates a new project from validated request data.
     *
     * @return \App\Support\ApiResponse The created API response containing the new ProjectResource and the message "Project created successfully".
     */
    public function store(StoreProjectRequest $request, CreateProjectAction $action)
    {
        $project = $action->handle($request->validated());

        return ApiResponse::created(new ProjectResource($project), 'Project created successfully');
    }

    /**
     * Return a resource representation of the given project with its attribute values and users loaded.
     *
     * @param Project $project The project model to present; the method will load `attributeValues.attribute` and `users` relationships.
     * @return \App\Support\ApiResponse An ApiResponse containing a ProjectResource of the project with loaded relationships.
     */
    public function show(Project $project)
    {
        $project->load(['attributeValues.attribute', 'users']);

        return ApiResponse::success(new ProjectResource($project));
    }

    /**
     * Update the given project using the request's validated data.
     *
     * @param UpdateProjectRequest $request Request containing validated update data.
     * @param Project $project The project instance to update.
     * @param UpdateProjectAction $action Action that performs the update operation.
     * @return \Illuminate\Http\JsonResponse A successful API response containing the updated ProjectResource and a success message.
     */
    public function update(UpdateProjectRequest $request, Project $project, UpdateProjectAction $action)
    {
        $project = $action->handle($project, $request->validated());

        return ApiResponse::success(new ProjectResource($project), 'Project updated successfully');
    }

    /**
     * Delete the given project if it has no assigned users.
     *
     * @param \App\Models\Project $project The project to delete.
     * @param \App\Actions\Project\DeleteProjectAction $action Action that performs the deletion.
     * @return \Illuminate\Http\JsonResponse A JSON API response: on success an empty data payload with message "Project deleted successfully"; on failure an error message "Cannot delete project with assigned users" and HTTP status 400.
     */
    public function destroy(Project $project, DeleteProjectAction $action)
    {
        if ($project->users()->exists()) {
            return ApiResponse::error('Cannot delete project with assigned users', 400);
        }

        $action->handle($project);

        return ApiResponse::success(null, 'Project deleted successfully');
    }

    / **
     * Filter projects by dynamic request parameters.
     *
     * Validates each request input as numeric, date, or string and returns projects that match the validated criteria.
     *
     * @param \Illuminate\Http\Request $request HTTP request containing filter key/value pairs.
     * @return \Illuminate\Http\JsonResponse `success` with a collection of ProjectResource when matches are found; `notFound` if no matches; `validationError` when filter validation fails; `serverError` on unexpected errors.
     */
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
