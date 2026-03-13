<?php

namespace App\Actions\Project;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class CreateProjectAction
{
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
