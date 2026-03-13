<?php

namespace App\Actions\Project;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class UpdateProjectAction
{
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
