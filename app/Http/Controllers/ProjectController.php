<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * List all projects with their dynamic attributes.
     */
    public function index()
    {
        try {
            // Fetch projects with related data
            $projects = Project::with(['attributeValues.attribute', 'users'])->get();

            // Handle empty results
            if ($projects->isEmpty()) {
                return response()->json(['message' => 'No projects found'], 404);
            }

            // Transform data
            $projects = $projects->map(function ($project) {
                $dynamicAttributes = [];

                if ($project->relationLoaded('attributeValues')) {
                    foreach ($project->attributeValues as $attrValue) {
                        if ($attrValue->relationLoaded('attribute') && $attrValue->attribute) {
                            $dynamicAttributes[$attrValue->attribute->name] = $attrValue->value;
                        }
                    }
                }

                return [
                    'id'         => $project->id,
                    'name'       => $project->name,
                    'status'     => $project->status,
                    'users'      => $project->users()->pluck('users.id'),
                    'attributes' => $dynamicAttributes,
                ];
            });

            return response()->json($projects, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Create a new project with optional dynamic attributes.
     */
    public function store(Request $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'name'       => 'required|string|max:255',
                'status'     => 'required|string|max:50',
                'users'      => 'array|nullable',
                'users.*'    => 'exists:users,id', // Ensures each user ID exists
                'attributes' => 'array|nullable',
                'attributes.*' => 'nullable|string|max:255', // Ensures attribute values are strings
            ]);

            DB::beginTransaction();

            // 1) Create the base project
            $project = Project::create([
                'name'   => $validated['name'],
                'status' => $validated['status'],
            ]);

            // 2) Attach any users provided
            if (!empty($validated['users'])) {
                $project->users()->sync($validated['users']);
            }

            // 3) Handle dynamic attributes (if provided)
            if (!empty($validated['attributes'])) {
                foreach ($validated['attributes'] as $attrName => $attrValue) {
                    $attributeModel = Attribute::where('name', $attrName)->first();
                    if ($attributeModel) {
                        AttributeValue::create([
                            'attribute_id' => $attributeModel->id,
                            'entity_id'    => $project->id,
                            'value'        => $attrValue,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message'  => 'Project created successfully',
                'project'  => $project->load('users', 'attributeValues.attribute'),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Validation failed',
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Show a single project with its attributes.
     */
    public function show(Project $project)
    {
        try {
            // Ensure the project exists and is accessible
            if (!$project) {
                return response()->json(['error' => 'Project not found'], 404);
            }

            // Load relationships safely
            $project->load(['attributeValues.attribute', 'users']);

            // Check if attributes exist before processing
            $dynamicAttributes = [];
            if ($project->relationLoaded('attributeValues')) {
                foreach ($project->attributeValues as $attrValue) {
                    if ($attrValue->relationLoaded('attribute') && $attrValue->attribute) {
                        $dynamicAttributes[$attrValue->attribute->name] = $attrValue->value;
                    }
                }
            }

            return response()->json([
                'id'         => $project->id,
                'name'       => $project->name,
                'status'     => $project->status,
                'users'      => $project->users()->pluck('users.id'),
                'attributes' => $dynamicAttributes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Update a project’s core fields and dynamic attributes.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'       => 'sometimes|string',
            'status'     => 'sometimes|string',
            'users'      => 'array',
            'attributes' => 'array',
        ]);

        DB::beginTransaction();
        try {
            // 1) Update project fields if present
            $project->update($request->only(['name', 'status']));

            // 2) Sync users if specified
            if ($request->has('users')) {
                $project->users()->sync($validated['users']);
            }

            // 3) Handle dynamic attributes
            if (!empty($validated['attributes'])) {
                foreach ($validated['attributes'] as $attrName => $attrValue) {
                    $attributeModel = Attribute::where('name', $attrName)->first();
                    if ($attributeModel) {
                        $attributeValue = AttributeValue::firstOrNew([
                            'attribute_id' => $attributeModel->id,
                            'entity_id'    => $project->id,
                        ]);
                        $attributeValue->value = $attrValue;
                        $attributeValue->save();
                    }
                }
            }

            DB::commit();
            return response()->json($project, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a project.
     */
    public function destroy(Project $project)
    {
        try {
            // Ensure the project exists
            if (!$project) {
                return response()->json(['error' => 'Project not found'], 404);
            }

            // Check if the project can be deleted 
            if ($project->users()->exists()) {
                return response()->json([
                    'error' => 'Cannot delete project with assigned users.'
                ], 400);
            }

            $project->delete();

            return response()->json(['message' => 'Project deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Filter projects by dynamic attributes.
     *
     * Example usage:
     *   GET /api/projects/filter?department=Finance&start_date=2025-01-01
     */
    public function filter(Request $request)
    {
        try {
            // Validate request with multiple data types (text, date, number, select)
            $rules = [];

            foreach ($request->all() as $key => $value) {
                if (is_numeric($value)) {
                    $rules[$key] = 'numeric';
                } elseif (strtotime($value) !== false) { // Check if it's a valid date
                    $rules[$key] = 'date';
                } else {
                    $rules[$key] = 'string';
                }
            }

            // Validate the incoming request
            $validatedData = $request->validate($rules);
            // Start with a base query
            $query = Project::query();

            // Ensure request contains valid filters
            if (!empty($validatedData)) {
                foreach ($validatedData as $attrName => $attrValue) {
                    $query->whereHas('attributeValues.attribute', function ($q) use ($attrName) {
                        $q->where('name', $attrName);
                    })->whereHas('attributeValues', function ($q) use ($attrValue) {
                        // Check value type and apply proper comparison
                        if (is_numeric($attrValue)) {
                            $q->where('value', '=', $attrValue);
                        } elseif (strtotime($attrValue) !== false) {
                            $q->whereDate('value', '=', $attrValue);
                        } else {
                            $q->where('value', 'LIKE', "%{$attrValue}%"); // Partial match for text/select
                        }
                    });
                }
            }

            // Retrieve filtered projects with necessary relationships
            $projects = $query->with(['attributeValues.attribute', 'users'])->get();

            if ($projects->isEmpty()) {
                return response()->json(['message' => 'No projects found matching the given criteria'], 404);
            }

            // Transform data
            $projects = $projects->map(function ($project) {
                $dynamicAttributes = [];

                if ($project->relationLoaded('attributeValues')) {
                    foreach ($project->attributeValues as $attrValue) {
                        if ($attrValue->relationLoaded('attribute') && $attrValue->attribute) {
                            $dynamicAttributes[$attrValue->attribute->name] = $attrValue->value;
                        }
                    }
                }

                return [
                    'id'         => $project->id,
                    'name'       => $project->name,
                    'status'     => $project->status,
                    'users'      => $project->users()->pluck('users.id'),
                    'attributes' => $dynamicAttributes,
                ];
            });

            return response()->json($projects, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error'   => 'Invalid input provided',
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
