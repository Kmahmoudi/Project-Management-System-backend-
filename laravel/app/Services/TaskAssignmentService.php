<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class TaskAssignmentService
{
    /**
     * Validates and assigns a user to a task.
     * Rules:
     *  - assigned user must exist
     *  - cannot assign admins
     *  - task must belong to a project
     */
    public function assign(Task $task, int $userId): Task
    {
        $user = User::find($userId);
        if (!$user) {
            throw ValidationException::withMessages(['assigned_to' => 'User not found.']);
        }
        if ($user->role === 'admin') {
            throw ValidationException::withMessages(['assigned_to' => 'Admins cannot be assigned to tasks.']);
        }
        if (!$task->project_id) {
            throw ValidationException::withMessages(['task' => 'Task is not attached to a project.']);
        }

        $task->assigned_to = $user->id;
        $task->save();

        return $task;
    }
}
