<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Task;
use App\Notifications\TaskAssigned;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTaskAssignedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $userId, public int $taskId) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        $task = Task::find($this->taskId);

        if ($user && $task) {
            $user->notify(new TaskAssigned($task));
        }
    }
}
