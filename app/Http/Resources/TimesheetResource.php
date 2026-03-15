<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimesheetResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the timesheet resource into an associative array for JSON responses.
     *
     * The array includes scalar attributes (id, task_name, date, hours, user_id, project_id, created_at, updated_at)
     * and conditionally includes a `user` resource and a `project` summary when those relations are loaded.
     *
     * @param Request $request Incoming HTTP request (unused by this transformer).
     * @return array An associative array representation of the timesheet resource; `user` and `project` keys are present only when their relations are loaded.
     */
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
