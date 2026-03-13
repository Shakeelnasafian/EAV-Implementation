<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array suitable for JSON serialization.
     *
     * @return array Associative array with keys `id`, `name`, `type`, `created_at`, and `updated_at` mapped from the underlying model.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'type'       => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
