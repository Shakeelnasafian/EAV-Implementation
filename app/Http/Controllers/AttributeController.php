<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AttributeController extends Controller
{
    /**
     * List all attributes.
     */
    public function index()
    {
        try {
            $attributes = Attribute::all();

            if ($attributes->isEmpty()) {
                return response()->json(['message' => 'No attributes found'], 404);
            }

            return response()->json($attributes, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create an attribute.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:attributes,name|max:255',
                'type' => 'required|string|in:text,date,number,select',
            ]);

            $attribute = Attribute::create($validated);

            return response()->json([
                'message'   => 'Attribute created successfully',
                'attribute' => $attribute,
            ], 201);
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
     * Show one attribute.
     */
    public function show($id)
    {
        try {
            $attribute = Attribute::findOrFail($id);

            return response()->json($attribute, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Attribute not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an attribute.
     */
    public function update(Request $request, $id)
    {
        try {
            $attribute = Attribute::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|unique:attributes,name,' . $attribute->id . '|max:255',
                'type' => 'sometimes|string|in:text,date,number,select',
            ]);

            $attribute->update($validated);

            return response()->json([
                'message'   => 'Attribute updated successfully',
                'attribute' => $attribute,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Attribute not found'], 404);
            
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
     * Delete an attribute.
     */
    public function destroy($id)
    {
        try {
            $attribute = Attribute::findOrFail($id);
            $attribute->delete();

            return response()->json(['message' => 'Attribute deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Attribute not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
