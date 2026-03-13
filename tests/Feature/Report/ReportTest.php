<?php

namespace Tests\Feature\Report;

use App\Models\Project;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_timesheet_report(): void
    {
        $this->actingAsAdmin();
        Timesheet::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/reports/timesheets');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'summary'    => ['total_entries', 'total_hours'],
                    'by_project',
                    'by_user',
                    'by_day',
                ],
            ]);
    }

    public function test_report_reflects_correct_totals(): void
    {
        $this->actingAsAdmin();
        $user    = User::factory()->create();
        $project = Project::factory()->create();

        Timesheet::factory()->create(['user_id' => $user->id, 'project_id' => $project->id, 'hours' => 3]);
        Timesheet::factory()->create(['user_id' => $user->id, 'project_id' => $project->id, 'hours' => 5]);

        $response = $this->getJson("/api/v1/reports/timesheets?user_id={$user->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_entries', 2)
            ->assertJsonPath('data.summary.total_hours', 8.0);
    }

    public function test_report_supports_date_range_filter(): void
    {
        $this->actingAsAdmin();

        Timesheet::factory()->create(['date' => '2025-01-10', 'hours' => 4]);
        Timesheet::factory()->create(['date' => '2025-06-10', 'hours' => 6]);
        Timesheet::factory()->create(['date' => '2025-12-10', 'hours' => 8]);

        $response = $this->getJson('/api/v1/reports/timesheets?date_from=2025-01-01&date_to=2025-06-30');

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_entries', 2)
            ->assertJsonPath('data.summary.total_hours', 10.0);
    }

    public function test_regular_user_only_sees_own_data_in_report(): void
    {
        $user  = $this->actingAsUser();
        $other = User::factory()->create();

        Timesheet::factory()->count(2)->create(['user_id' => $user->id, 'hours' => 2]);
        Timesheet::factory()->count(3)->create(['user_id' => $other->id, 'hours' => 3]);

        $response = $this->getJson('/api/v1/reports/timesheets');

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_entries', 2);
    }

    public function test_report_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/reports/timesheets');

        $response->assertStatus(401);
    }
}
