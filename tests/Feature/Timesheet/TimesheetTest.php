<?php

namespace Tests\Feature\Timesheet;

use App\Models\Project;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimesheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_only_their_own_timesheets(): void
    {
        $user  = $this->actingAsUser();
        $other = User::factory()->create();

        Timesheet::factory()->count(2)->create(['user_id' => $user->id]);
        Timesheet::factory()->count(3)->create(['user_id' => $other->id]);

        $response = $this->getJson('/api/v1/timesheets');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_can_list_all_timesheets(): void
    {
        $this->actingAsAdmin();
        Timesheet::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/timesheets');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 5);
    }

    public function test_timesheets_support_date_range_filter(): void
    {
        $this->actingAsAdmin();
        Timesheet::factory()->create(['date' => '2025-01-15']);
        Timesheet::factory()->create(['date' => '2025-06-15']);
        Timesheet::factory()->create(['date' => '2025-12-15']);

        $response = $this->getJson('/api/v1/timesheets?date_from=2025-01-01&date_to=2025-06-30');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_any_user_can_create_timesheet(): void
    {
        $user    = $this->actingAsUser();
        $project = Project::factory()->create();

        $response = $this->postJson('/api/v1/timesheets', [
            'task_name'  => 'Implement feature X',
            'date'       => '2025-03-10',
            'hours'      => 4.5,
            'user_id'    => $user->id,
            'project_id' => $project->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.task_name', 'Implement feature X')
            ->assertJsonPath('data.hours', 4.5);

        $this->assertDatabaseHas('timesheets', ['task_name' => 'Implement feature X']);
    }

    public function test_create_timesheet_fails_with_invalid_hours(): void
    {
        $user    = $this->actingAsUser();
        $project = Project::factory()->create();

        $response = $this->postJson('/api/v1/timesheets', [
            'task_name'  => 'Task',
            'date'       => '2025-03-10',
            'hours'      => -1,
            'user_id'    => $user->id,
            'project_id' => $project->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_view_own_timesheet(): void
    {
        $user      = $this->actingAsUser();
        $timesheet = Timesheet::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/timesheets/{$timesheet->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $timesheet->id);
    }

    public function test_user_cannot_view_others_timesheet(): void
    {
        $this->actingAsUser();
        $other     = User::factory()->create();
        $timesheet = Timesheet::factory()->create(['user_id' => $other->id]);

        $response = $this->getJson("/api/v1/timesheets/{$timesheet->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_update_own_timesheet(): void
    {
        $user      = $this->actingAsUser();
        $timesheet = Timesheet::factory()->create(['user_id' => $user->id]);

        $response = $this->putJson("/api/v1/timesheets/{$timesheet->id}", [
            'task_name' => 'Updated Task',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.task_name', 'Updated Task');
    }

    public function test_user_cannot_update_others_timesheet(): void
    {
        $this->actingAsUser();
        $other     = User::factory()->create();
        $timesheet = Timesheet::factory()->create(['user_id' => $other->id]);

        $response = $this->putJson("/api/v1/timesheets/{$timesheet->id}", [
            'task_name' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_soft_delete_own_timesheet(): void
    {
        $user      = $this->actingAsUser();
        $timesheet = Timesheet::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/timesheets/{$timesheet->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('timesheets', ['id' => $timesheet->id]);
    }

    public function test_admin_can_restore_soft_deleted_timesheet(): void
    {
        $this->actingAsAdmin();
        $timesheet = Timesheet::factory()->create();
        $timesheet->delete();

        $response = $this->postJson("/api/v1/timesheets/{$timesheet->id}/restore");

        $response->assertStatus(200);
        $this->assertNotSoftDeleted('timesheets', ['id' => $timesheet->id]);
    }
}
