<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectCreated extends Notification
{
    public function __construct(public Project $project) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $workspace = $this->project->workspace?->name ?? 'your workspace';

        return (new MailMessage)
            ->subject('New project: '.$this->project->name)
            ->greeting('Hello '.($notifiable->name ?? ''))
            ->line('A new project “'.$this->project->name.'” was created in '.$workspace.'.')
            ->action('Open project', url('/projects/'.$this->project->id));
    }
}
