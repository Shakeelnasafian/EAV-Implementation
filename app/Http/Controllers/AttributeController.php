<?php

namespace App\Http\Controllers;

use App\Actions\Attribute\CreateAttributeAction;
use App\Actions\Attribute\DeleteAttributeAction;
use App\Actions\Attribute\UpdateAttributeAction;
use App\Http\Requests\Attribute\StoreAttributeRequest;
use App\Http\Requests\Attribute\UpdateAttributeRequest;
use App\Http\Resources\AttributeResource;
use App\Services\AttributeService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AttributeController extends Controller
{
    public function __construct(private readonly AttributeService $attributeService) {}

    public function index()
    {
        $attributes = $this->attributeService->getAll();

        if ($attributes->isEmpty()) {
            return ApiResponse::notFound('No attributes found');
        }

        return ApiResponse::success(AttributeResource::collection($attributes));
    }

    public function store(StoreAttributeRequest $request, CreateAttributeAction $action)
    {
        $attribute = $action->handle($request->validated());

        return ApiResponse::created(new AttributeResource($attribute), 'Attribute created successfully');
    }

    public function show($id)
    {
        try {
            $attribute = $this->attributeService->findById($id);

            return ApiResponse::success(new AttributeResource($attribute));
        } catch (ModelNotFoundException) {
            return ApiResponse::notFound('Attribute not found');
        }
    }

    public function update(UpdateAttributeRequest $request, $id, UpdateAttributeAction $action)
    {
        try {
            $attribute = $this->attributeService->findById($id);
            $attribute = $action->handle($attribute, $request->validated());

            return ApiResponse::success(new AttributeResource($attribute), 'Attribute updated successfully');
        } catch (ModelNotFoundException) {
            return ApiResponse::notFound('Attribute not found');
        }
    }

    public function destroy($id, DeleteAttributeAction $action)
    {
        try {
            $attribute = $this->attributeService->findById($id);
            $action->handle($attribute);

            return ApiResponse::success(null, 'Attribute deleted successfully');
        } catch (ModelNotFoundException) {
            return ApiResponse::notFound('Attribute not found');
        }
    }
}
