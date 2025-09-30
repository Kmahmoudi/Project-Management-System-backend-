<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_project(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('api')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/projects', [
                'title' => 'P1',
                'description' => 'D1',
            ])
            ->assertCreated()
            ->assertJson(['success' => true]);
    }

    public function test_non_admin_cannot_create_project(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('api')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/projects', ['title' => 'P1'])
            ->assertStatus(403);
    }
}
