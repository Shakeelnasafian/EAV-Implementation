<?php

namespace App\Actions\Project;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class CreateProjectAction
{
    /**
     * Create a new Project, optionally associate users, and attach named attributes.
     *
     * Expects $data to contain keys:
     * - `name` (string): project name.
     * - `status` (string): project status.
     * - `users` (int[]|null): optional array of user IDs to sync to the project.
     * - `attributes` (array|null): optional associative array of attribute name => value pairs to persist as AttributeValue records.
     *
     * @param array $data Input data for project creation and relations.
     * @return \App\Models\Project The created Project with `users` and `attributeValues.attribute` relationships loaded.
     */
    public function handle(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = Project::create([
                'name'   => $data['name'],
                'status' => $data['status'],
            ]);

            if (!empty($data['users'])) {
                $project->users()->sync($data['users']);
            }

            if (!empty($data['attributes'])) {
                $this->attachAttributes($project, $data['attributes']);
            }

            return $project->load(['users', 'attributeValues.attribute']);
        });
    }

    /**
     * Create AttributeValue records for the given attribute name => value pairs on the project when matching Attributes exist.
     *
     * For each entry in `$attributes`, this locates an `Attribute` by name and, if found, creates an `AttributeValue` whose
     * `attribute_id` references the found `Attribute`, `entity_id` is the project's id, and `value` is the provided value.
     * Entries without a matching `Attribute` are ignored.
     *
     * @param Project $project The project to attach attributes to.
     * @param array $attributes Associative array mapping attribute names (string) to their corresponding values.
     */
    private function attachAttributes(Project $project, array $attributes): void
    {
        foreach ($attributes as $attrName => $attrValue) {
            $attributeModel = Attribute::where('name', $attrName)->first();

            if ($attributeModel) {
                AttributeValue::create([
                    'attribute_id' => $attributeModel->id,
                    'entity_id'    => $project->id,
                    'value'        => $attrValue,
                ]);
            }
        }
    }
}
