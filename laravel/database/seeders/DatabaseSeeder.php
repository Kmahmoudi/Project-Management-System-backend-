<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Comment;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create exact roles with known password "password"
        User::factory()->count(3)->create([
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        User::factory()->count(3)->create([
            'role' => 'manager',
            'password' => Hash::make('password'),
        ]);

        User::factory()->count(5)->create([
            'role' => 'user',
            'password' => Hash::make('password'),
        ]);

        $admins   = User::where('role','admin')->get();
        $managers = User::where('role','manager')->get();
        $users    = User::where('role','user')->get();

        // 5 projects created by admins
        $projects = collect();
        for ($i=0; $i<5; $i++) {
            $projects->push(Project::factory()->create([
                'created_by' => $admins->random()->id,
            ]));
        }

        // 10 tasks across projects, assigned to a user or manager
        $assignees = $users->merge($managers);
        $tasks = collect();
        for ($i=0; $i<10; $i++) {
            $tasks->push(Task::factory()->create([
                'project_id' => $projects->random()->id,
                'assigned_to'=> $assignees->random()->id,
            ]));
        }

        // 10 comments by users on random tasks
        for ($i=0; $i<10; $i++) {
            Comment::factory()->create([
                'task_id' => $tasks->random()->id,
                'user_id' => $users->random()->id,
            ]);
        }
    }
}
