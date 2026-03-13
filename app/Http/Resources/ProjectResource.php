<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public static $wrap = null;

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
