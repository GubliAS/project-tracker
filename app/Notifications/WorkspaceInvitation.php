<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceInvitation extends Notification
{
    public function __construct(public Invitation $invitation) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $workspace = $this->invitation->workspace?->name ?? 'a workspace';
        $path = $this->invitation->invitePath();
        $url = $this->invitation->inviteUrl();
        $appUrl = rtrim((string) config('app.url'), '/');

        return (new MailMessage)
            ->subject('You have been invited to '.$workspace)
            ->greeting('Hello '.($notifiable->name ?? ''))
            ->line('You have been invited to join '.$workspace.' on Project Tracker.')
            ->line('Set your password to activate your account and start collaborating.')
            ->action('Set your password', $url)
            ->line('This invitation expires on '.$this->invitation->expires_at?->toDayDateTimeString().'.')
            ->line('The button opens '.$url.'. That host comes from APP_URL (currently '.$appUrl.').')
            ->line('If APP_URL is localhost or 127.0.0.1, the link only works on the machine that sent this email.')
            ->line('Open this path on the environment that has this code: '.$path)
            ->line('Example: http://THEIR-IP:8000'.$path);
    }
}
