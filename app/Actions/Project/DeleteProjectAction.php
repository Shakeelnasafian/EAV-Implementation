<?php

namespace App\Actions\Project;

use App\Models\Project;

class DeleteProjectAction
{
    /**
     * Delete the given Project from persistent storage.
     *
     * @param \App\Models\Project $project The Project instance to delete.
     */
    public function handle(Project $project): void
    {
        $project->delete();
    }
}
