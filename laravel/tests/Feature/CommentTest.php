<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_and_list_comments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create(['role' => 'user']);
        $project = Project::factory()->create(['created_by' => $admin->id]);
        $task = Task::factory()->create(['project_id' => $project->id, 'assigned_to' => $user->id]);

        $token = $user->createToken('api')->plainTextToken;

        $this->withHeader('Authorization','Bearer '.$token)
            ->postJson("/api/tasks/{$task->id}/comments", ['body' => 'Hello'])
            ->assertCreated()
            ->assertJson(['success' => true]);

        $this->withHeader('Authorization','Bearer '.$token)
            ->getJson("/api/tasks/{$task->id}/comments")
            ->assertOk()
            ->assertJson(['success' => true]);
    }
}
