<?php

namespace Tests\Feature\Project;

use App\Models\Attribute;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_projects(): void
    {
        $this->actingAsUser();
        Project::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/projects');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'total', 'per_page']]);
    }

    public function test_project_list_supports_pagination(): void
    {
        $this->actingAsUser();
        Project::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/projects?per_page=5');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonCount(5, 'data');
    }

    public function test_project_list_supports_status_filter(): void
    {
        $this->actingAsUser();
        Project::factory()->count(2)->create(['status' => 'active']);
        Project::factory()->count(3)->create(['status' => 'inactive']);

        $response = $this->getJson('/api/v1/projects?status=active');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_manager_can_create_project(): void
    {
        $manager = $this->actingAsManager();
        $user    = User::factory()->create();

        $response = $this->postJson('/api/v1/projects', [
            'name'   => 'New Project',
            'status' => 'active',
            'users'  => [$user->id],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Project')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('projects', ['name' => 'New Project']);
    }

    public function test_manager_can_create_project_with_eav_attributes(): void
    {
        $this->actingAsManager();
        $attribute = Attribute::factory()->create(['name' => 'department', 'type' => 'text']);

        $response = $this->postJson('/api/v1/projects', [
            'name'       => 'EAV Project',
            'status'     => 'active',
            'attributes' => ['department' => 'Engineering'],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => $attribute->id,
            'value'        => 'Engineering',
        ]);
    }

    public function test_regular_user_cannot_create_project(): void
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/v1/projects', [
            'name'   => 'Forbidden Project',
            'status' => 'active',
        ]);

        $response->assertStatus(403);
    }

    public function test_can_show_project(): void
    {
        $user    = $this->actingAsUser();
        $project = Project::factory()->create();
        $project->users()->attach($user->id);

        $response = $this->getJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $project->id);
    }

    public function test_user_cannot_view_project_they_are_not_assigned_to(): void
    {
        $this->actingAsUser();
        $project = Project::factory()->create();

        $response = $this->getJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(403);
    }

    public function test_manager_can_update_project(): void
    {
        $this->actingAsManager();
        $project = Project::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/v1/projects/{$project->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_manager_can_soft_delete_project(): void
    {
        $this->actingAsManager();
        $project = Project::factory()->create();

        $response = $this->deleteJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_cannot_delete_project_with_assigned_users(): void
    {
        $this->actingAsManager();
        $project = Project::factory()->create();
        $user    = User::factory()->create();
        $project->users()->attach($user->id);

        $response = $this->deleteJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(400);
    }

    public function test_manager_can_restore_soft_deleted_project(): void
    {
        $this->actingAsManager();
        $project = Project::factory()->create();
        $project->delete();

        $response = $this->postJson("/api/v1/projects/{$project->id}/restore");

        $response->assertStatus(200);
        $this->assertNotSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_filter_projects_by_dynamic_attribute(): void
    {
        $this->actingAsUser();
        Attribute::factory()->create(['name' => 'team', 'type' => 'text']);

        $project = Project::factory()->create(['name' => 'Alpha']);
        $attr    = \App\Models\Attribute::where('name', 'team')->first();
        \App\Models\AttributeValue::create([
            'attribute_id' => $attr->id,
            'entity_id'    => $project->id,
            'value'        => 'Backend',
        ]);

        Project::factory()->create(['name' => 'Beta']);

        $response = $this->getJson('/api/v1/projects/filter?team=Backend');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
