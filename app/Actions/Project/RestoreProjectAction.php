<?php

namespace App\Actions\Project;

use App\Models\Project;

class RestoreProjectAction
{
    public function handle(Project $project): Project
    {
        $project->restore();

        return $project->load(['users', 'attributeValues.attribute']);
    }
}
