<?php

namespace App\Actions\Project;

use App\Models\Project;

class DeleteProjectAction
{
    public function handle(Project $project): void
    {
        $project->delete();
    }
}
