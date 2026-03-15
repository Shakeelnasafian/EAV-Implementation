<?php

namespace App\Actions\Project;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class UpdateProjectAction
{
    /**
     * Update a Project and its related users and attribute values within a single database transaction.
     *
     * The provided $data may include keys:
     * - `name` (string): new project name.
     * - `status` (mixed): new project status.
     * - `users` (array): list of user IDs to synchronize with the project; if present, the project's users are synced to this list (empty array clears users).
     * - `attributes` (array): map of attribute name => value pairs to upsert as AttributeValue records for the project.
     *
     * All modifications are performed inside a DB transaction; any exception will roll back the changes.
     *
     * @param Project $project The project to update.
     * @param array $data Associative array of fields and related data to apply.
     * @return Project The updated project instance with the `users` and `attributeValues.attribute` relationships loaded.
     */
    public function handle(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $projectFields = array_intersect_key($data, array_flip(['name', 'status']));

            if (!empty($projectFields)) {
                $project->update($projectFields);
            }

            if (array_key_exists('users', $data)) {
                $project->users()->sync($data['users'] ?? []);
            }

            if (!empty($data['attributes'])) {
                $this->upsertAttributes($project, $data['attributes']);
            }

            return $project->load(['users', 'attributeValues.attribute']);
        });
    }

    /**
     * Create or update attribute values on the given project for attributes that exist by name.
     *
     * For each entry in $attributes, finds an Attribute with the same name and creates or updates
     * the corresponding AttributeValue tied to the project. Attribute names that do not resolve
     * to an existing Attribute are ignored.
     *
     * @param Project $project The project to attach attribute values to.
     * @param array<string,mixed> $attributes Map of attribute name => attribute value to upsert.
     */
    private function upsertAttributes(Project $project, array $attributes): void
    {
        foreach ($attributes as $attrName => $attrValue) {
            $attributeModel = Attribute::where('name', $attrName)->first();

            if ($attributeModel) {
                AttributeValue::updateOrCreate(
                    ['attribute_id' => $attributeModel->id, 'entity_id' => $project->id],
                    ['value' => $attrValue]
                );
            }
        }
    }
}
