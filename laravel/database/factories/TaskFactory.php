<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;
use App\Models\User;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $statuses = ['pending','in-progress','done'];

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'status' => $statuses[array_rand($statuses)],
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'project_id' => Project::factory(),
            'assigned_to' => User::factory(), // can be overridden
        ];
    }
}
