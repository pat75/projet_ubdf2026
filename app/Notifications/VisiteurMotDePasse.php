<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Lien de reinitialisation du mot de passe d'un compte visiteur (broker `visitors`). */
class VisiteurMotDePasse extends Notification
{
    public function __construct(private readonly string $jeton) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lien = lien('visiteur.mot-de-passe', ['jeton' => $this->jeton, 'email' => $notifiable->email]);

        return (new MailMessage)
            ->subject(__('Réinitialiser votre mot de passe'))
            ->line(__('Vous avez demandé à changer le mot de passe de votre compte visiteur.'))
            ->action(__('Choisir un nouveau mot de passe'), $lien)
            ->line(__('Ce lien est valable une heure. Si vous n’êtes pas à l’origine de cette demande, ignorez ce message.'));
    }
}
