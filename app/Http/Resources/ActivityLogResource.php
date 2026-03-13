<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'action'     => $this->action,
            'model_type' => class_basename($this->model_type),
            'model_id'   => $this->model_id,
            'changes'    => $this->changes,
            'ip_address' => $this->ip_address,
            'performed_by' => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->first_name . ' ' . $this->user->last_name,
                'email' => $this->user->email,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
