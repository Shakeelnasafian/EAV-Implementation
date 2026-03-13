<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Timesheet>
 */
class TimesheetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_name'  => fake()->sentence(3),
            'date'       => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'hours'      => fake()->randomFloat(1, 0.5, 8),
            'user_id'    => User::factory(),
            'project_id' => Project::factory(),
        ];
    }
}
