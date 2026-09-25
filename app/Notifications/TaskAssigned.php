<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
{
    public function __construct(public Task $task) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $project = $this->task->project?->name ?? 'a project';

        return (new MailMessage)
            ->subject('You were assigned a task')
            ->greeting('Hello '.($notifiable->name ?? ''))
            ->line('You have been assigned “'.$this->task->title.'” on '.$project.'.')
            ->action('Open tasks', url('/tasks'))
            ->line('Update the task status when you start or finish the work.');
    }
}
