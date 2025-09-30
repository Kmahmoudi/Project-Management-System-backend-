<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_and_assignee_can_update_status(): void
    {
        $admin   = User::factory()->create(['role' => 'admin']);
        $manager = User::factory()->create(['role' => 'manager']);
        $assignee= User::factory()->create(['role' => 'user']);

        $project = Project::factory()->create(['created_by' => $admin->id]);

        $mtoken = $manager->createToken('api')->plainTextToken;

        // Manager creates task and assigns to user
        $create = $this->withHeader('Authorization','Bearer '.$mtoken)
            ->postJson("/api/projects/{$project->id}/tasks", [
                'title' => 'T1', 'description' => 'D1', 'assigned_to' => $assignee->id
            ])
            ->assertCreated()
            ->json();

        $taskId = $create['data']['id'];

        // Assignee updates status
        $utoken = $assignee->createToken('api')->plainTextToken;

        $this->withHeader('Authorization','Bearer '.$utoken)
            ->putJson("/api/tasks/{$taskId}", ['status' => 'in-progress'])
            ->assertOk()
            ->assertJson(['success' => true]);

        // Assignee cannot reassign
        $this->withHeader('Authorization','Bearer '.$utoken)
            ->putJson("/api/tasks/{$taskId}", ['assigned_to' => $manager->id])
            ->assertStatus(403);
    }

    public function test_manager_can_delete_task(): void
    {
        $admin   = User::factory()->create(['role' => 'admin']);
        $manager = User::factory()->create(['role' => 'manager']);
        $project = Project::factory()->create(['created_by' => $admin->id]);
        $task    = Task::factory()->create(['project_id' => $project->id]);

        $token = $manager->createToken('api')->plainTextToken;

        $this->withHeader('Authorization','Bearer '.$token)
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJson(['success' => true]);
    }
}
