<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Task;
use App\Models\User;

class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'body' => $this->faker->sentence(12),
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
        ];
    }
}
