<?php

namespace App\Http\Controllers;

use App\Actions\Attribute\CreateAttributeAction;
use App\Actions\Attribute\DeleteAttributeAction;
use App\Actions\Attribute\UpdateAttributeAction;
use App\Http\Requests\Attribute\StoreAttributeRequest;
use App\Http\Requests\Attribute\UpdateAttributeRequest;
use App\Http\Resources\AttributeResource;
use App\Models\Attribute;
use App\Services\AttributeService;
use App\Support\ApiResponse;
use OpenApi\Attributes as OA;

class AttributeController extends Controller
{
    public function __construct(private readonly AttributeService $attributeService) {}

    #[OA\Get(
        path: '/attributes',
        summary: 'List all EAV attributes',
        security: [['bearerAuth' => []]],
        tags: ['Attributes'],
        responses: [
            new OA\Response(response: 200, description: 'List of attributes'),
            new OA\Response(response: 404, description: 'No attributes found'),
        ]
    )]
    public function index()
    {
        $this->authorize('viewAny', Attribute::class);

        $attributes = $this->attributeService->getAll();

        if ($attributes->isEmpty()) {
            return ApiResponse::notFound('No attributes found');
        }

        return ApiResponse::success(AttributeResource::collection($attributes));
    }

    #[OA\Post(
        path: '/attributes',
        summary: 'Create a new EAV attribute (admin only)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['name', 'type'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'department'),
                new OA\Property(property: 'type', type: 'string', enum: ['text', 'date', 'number', 'select']),
            ]
        )),
        tags: ['Attributes'],
        responses: [
            new OA\Response(response: 201, description: 'Attribute created'),
            new OA\Response(response: 403, description: 'Forbidden — admin only'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreAttributeRequest $request, CreateAttributeAction $action)
    {
        $this->authorize('create', Attribute::class);

        $attribute = $action->handle($request->validated());

        return ApiResponse::created(new AttributeResource($attribute), 'Attribute created successfully');
    }

    #[OA\Get(
        path: '/attributes/{id}',
        summary: 'Get a single attribute',
        security: [['bearerAuth' => []]],
        tags: ['Attributes'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Attribute detail'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show($id)
    {
        $attribute = $this->attributeService->findById($id);

        $this->authorize('view', $attribute);

        return ApiResponse::success(new AttributeResource($attribute));
    }

    #[OA\Put(
        path: '/attributes/{id}',
        summary: 'Update an attribute (admin only)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'type', type: 'string', enum: ['text', 'date', 'number', 'select']),
        ])),
        tags: ['Attributes'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Attribute updated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function update(UpdateAttributeRequest $request, $id, UpdateAttributeAction $action)
    {
        $attribute = $this->attributeService->findById($id);

        $this->authorize('update', $attribute);

        $attribute = $action->handle($attribute, $request->validated());

        return ApiResponse::success(new AttributeResource($attribute), 'Attribute updated successfully');
    }

    #[OA\Delete(
        path: '/attributes/{id}',
        summary: 'Delete an attribute (admin only)',
        security: [['bearerAuth' => []]],
        tags: ['Attributes'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Attribute deleted'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy($id, DeleteAttributeAction $action)
    {
        $attribute = $this->attributeService->findById($id);

        $this->authorize('delete', $attribute);

        $action->handle($attribute);

        return ApiResponse::success(null, 'Attribute deleted successfully');
    }
}
