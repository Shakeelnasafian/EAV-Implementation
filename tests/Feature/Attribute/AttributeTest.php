<?php

namespace Tests\Feature\Attribute;

use App\Models\Attribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_authenticated_user_can_list_attributes(): void
    {
        $this->actingAsUser();
        Attribute::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/attributes');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'data');
    }

    public function test_list_attributes_returns_404_when_empty(): void
    {
        $this->actingAsUser();

        $response = $this->getJson('/api/v1/attributes');

        $response->assertStatus(404);
    }

    public function test_admin_can_create_attribute(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/v1/attributes', [
            'name' => 'department',
            'type' => 'text',
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name', 'department');

        $this->assertDatabaseHas('attributes', ['name' => 'department', 'type' => 'text']);
    }

    public function test_regular_user_cannot_create_attribute(): void
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/v1/attributes', [
            'name' => 'department',
            'type' => 'text',
        ]);

        $response->assertStatus(403);
    }

    public function test_create_attribute_fails_with_duplicate_name(): void
    {
        $this->actingAsAdmin();
        Attribute::factory()->create(['name' => 'budget']);

        $response = $this->postJson('/api/v1/attributes', [
            'name' => 'budget',
            'type' => 'number',
        ]);

        $response->assertStatus(422);
    }

    public function test_create_attribute_fails_with_invalid_type(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/v1/attributes', [
            'name' => 'something',
            'type' => 'invalid_type',
        ]);

        $response->assertStatus(422);
    }

    public function test_can_show_single_attribute(): void
    {
        $this->actingAsUser();
        $attribute = Attribute::factory()->create();

        $response = $this->getJson("/api/v1/attributes/{$attribute->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $attribute->id);
    }

    public function test_show_returns_404_for_nonexistent_attribute(): void
    {
        $this->actingAsUser();

        $response = $this->getJson('/api/v1/attributes/999');

        $response->assertStatus(404);
    }

    public function test_admin_can_update_attribute(): void
    {
        $this->actingAsAdmin();
        $attribute = Attribute::factory()->create(['name' => 'old_name']);

        $response = $this->putJson("/api/v1/attributes/{$attribute->id}", [
            'name' => 'new_name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'new_name');
    }

    public function test_regular_user_cannot_update_attribute(): void
    {
        $this->actingAsUser();
        $attribute = Attribute::factory()->create();

        $response = $this->putJson("/api/v1/attributes/{$attribute->id}", ['name' => 'hacked']);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_attribute(): void
    {
        $this->actingAsAdmin();
        $attribute = Attribute::factory()->create();

        $response = $this->deleteJson("/api/v1/attributes/{$attribute->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('attributes', ['id' => $attribute->id]);
    }

    public function test_regular_user_cannot_delete_attribute(): void
    {
        $this->actingAsUser();
        $attribute = Attribute::factory()->create();

        $response = $this->deleteJson("/api/v1/attributes/{$attribute->id}");

        $response->assertStatus(403);
    }
}
