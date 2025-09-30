<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Task Assigned: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line('You have been assigned a new task.')
            ->line('Task: ' . $this->task->title)
            ->line('Due: ' . optional($this->task->due_date)->format('Y-m-d'))
            ->action('View Task', url('/tasks/'.$this->task->id))
            ->line('Thanks!');
    }
}
