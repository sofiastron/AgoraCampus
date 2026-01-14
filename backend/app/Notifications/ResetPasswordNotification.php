<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $frontendUrl = config('app.frontend_url') . "/reset-password?token={$this->token}&email={$notifiable->email}";

        return (new MailMessage)
            ->subject('Réinitialisation du mot de passe')
            ->line('Vous recevez cet email car vous avez demandé à réinitialiser votre mot de passe.')
            ->action('Réinitialiser le mot de passe', $frontendUrl)
            ->line('Si vous n’avez pas demandé cette réinitialisation, ignorez cet email.');
    }
}
