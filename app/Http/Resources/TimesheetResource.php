<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimesheetResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'task_name'  => $this->task_name,
            'date'       => $this->date,
            'hours'      => $this->hours,
            'user_id'    => $this->user_id,
            'project_id' => $this->project_id,
            'user'       => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'project'    => $this->whenLoaded('project', fn () => [
                'id'   => $this->project->id,
                'name' => $this->project->name,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
