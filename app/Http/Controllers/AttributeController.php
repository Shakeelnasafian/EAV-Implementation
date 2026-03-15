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
    /**
 * Create a new controller instance and inject the AttributeService.
 *
 * @param AttributeService $attributeService Service responsible for retrieving and manipulating attributes.
 */
public function __construct(private readonly AttributeService $attributeService) {}

    /**
     * Retrieve all attributes and return them as a collection of resources.
     *
     * Returns an API response containing a collection of AttributeResource instances when attributes exist,
     * or an ApiResponse not-found response with the message "No attributes found" when the collection is empty.
     *
     * @return mixed An ApiResponse containing either the AttributeResource collection or a not-found message.
     */
    public function index()
    {
        $attributes = $this->attributeService->getAll();

        if ($attributes->isEmpty()) {
            return ApiResponse::notFound('No attributes found');
        }

        return ApiResponse::success(AttributeResource::collection($attributes));
    }

    /**
     * Create a new attribute from validated request data.
     *
     * @param StoreAttributeRequest $request The incoming request with validated attribute data.
     * @param CreateAttributeAction $action The action that creates the attribute.
     * @return \Illuminate\Http\JsonResponse The created API response containing the new AttributeResource and a success message.
     */
    public function store(StoreAttributeRequest $request, CreateAttributeAction $action)
    {
        $attribute = $action->handle($request->validated());

        return ApiResponse::created(new AttributeResource($attribute), 'Attribute created successfully');
    }

    /**
     * Retrieve and return an attribute by its ID.
     *
     * @param mixed $id The attribute identifier.
     * @return \Illuminate\Http\JsonResponse A JSON response containing an AttributeResource when found, or a 404 not found message when the attribute does not exist.
     */
    public function show($id)
    {
        try {
            $attribute = $this->attributeService->findById($id);

            return ApiResponse::success(new AttributeResource($attribute));
        } catch (ModelNotFoundException) {
            return ApiResponse::notFound('Attribute not found');
        }
    }

    /**
     * Update an existing attribute by ID using validated request data.
     *
     * @param UpdateAttributeRequest $request The validated request containing attribute fields to update.
     * @param mixed $id The identifier of the attribute to update.
     * @param UpdateAttributeAction $action The action that applies the validated data to the attribute.
     * @return \Illuminate\Http\Response An ApiResponse containing the updated AttributeResource and a success message, or a 404 ApiResponse if the attribute is not found.
     */
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

    /**
     * Delete an attribute by its identifier.
     *
     * @param int|string $id The attribute identifier.
     * @return \Illuminate\Http\JsonResponse ApiResponse with `null` data and message "Attribute deleted successfully" on success, or a 404 ApiResponse with message "Attribute not found" if the attribute does not exist.
     */
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
