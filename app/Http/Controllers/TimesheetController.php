<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TimesheetController extends Controller
{
    /**
     * Display a listing of timesheets.
     */
    public function index()
    {
        try {
            // Eager-load related user and project
            $timesheets = Timesheet::with(['user', 'project'])->get();

            if ($timesheets->isEmpty()) {
                return response()->json(['message' => 'No timesheets found'], 404);
            }

            return response()->json($timesheets, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created timesheet.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'task_name'  => 'required|string|max:255',
                'date'       => 'required|date',
                'hours'      => 'required|numeric|min:0.1',
                'user_id'    => 'required|exists:users,id',
                'project_id' => 'required|exists:projects,id',
            ]);

            $timesheet = Timesheet::create($validated);

            return response()->json($timesheet, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error'   => 'Validation failed',
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified timesheet.
     */
    public function show(Timesheet $timesheet)
    {
        try {
            if (!$timesheet) {
                return response()->json(['error' => 'Timesheet not found'], 404);
            }

            $timesheet->load('user', 'project');

            return response()->json($timesheet, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified timesheet.
     */
    public function update(Request $request, Timesheet $timesheet)
    {
        try {
            if (!$timesheet) {
                return response()->json(['error' => 'Timesheet not found'], 404);
            }

            $validated = $request->validate([
                'task_name'  => 'sometimes|string|max:255',
                'date'       => 'sometimes|date',
                'hours'      => 'sometimes|numeric|min:0.1',
                'user_id'    => 'sometimes|exists:users,id',
                'project_id' => 'sometimes|exists:projects,id',
            ]);

            $timesheet->update($validated);

            return response()->json($timesheet, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error'   => 'Validation failed',
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified timesheet.
     */
    public function destroy(Timesheet $timesheet)
    {
        try {
            if (!$timesheet) {
                return response()->json(['error' => 'Timesheet not found'], 404);
            }

            $timesheet->delete();

            return response()->json(['message' => 'Timesheet deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
