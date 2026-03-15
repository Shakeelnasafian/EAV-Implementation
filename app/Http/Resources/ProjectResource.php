<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Convert the project resource into an array suitable for JSON serialization.
     *
     * Dynamic attribute key/value pairs are built from the loaded `attributeValues` relation:
     * for each attribute value with a loaded `attribute`, the attribute's `name` is used as the key
     * and the attribute value's `value` as the value.
     *
     * @return array{
     *     id: mixed,
     *     name: mixed,
     *     status: mixed,
     *     users?: \Illuminate\Support\Collection|null,
     *     attributes: array,
     *     created_at: mixed,
     *     updated_at: mixed
     * }
     *     The returned array contains:
     *     - `id`: the project's identifier.
     *     - `name`: the project's name.
     *     - `status`: the project's status.
     *     - `users`: a collection of user IDs when the `users` relation is loaded, otherwise omitted.
     *     - `attributes`: an associative array mapping attribute names to their values.
     *     - `created_at`: the creation timestamp.
     *     - `updated_at`: the last update timestamp.
     */
    public function toArray(Request $request): array
    {
        $dynamicAttributes = [];

        if ($this->relationLoaded('attributeValues')) {
            foreach ($this->attributeValues as $attrValue) {
                if ($attrValue->relationLoaded('attribute') && $attrValue->attribute) {
                    $dynamicAttributes[$attrValue->attribute->name] = $attrValue->value;
                }
            }
        }

        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'status'     => $this->status,
            'users'      => $this->whenLoaded('users', fn () => $this->users->pluck('id')),
            'attributes' => $dynamicAttributes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
