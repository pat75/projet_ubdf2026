<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Lien de reinitialisation, un par compte portant l'adresse.
 *
 * @param  list<array{login:string,lien:string}>  $comptes
 */
class MotDePasseOublie extends Mailable
{
    public function __construct(public readonly array $comptes) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Réinitialiser votre mot de passe'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.mot-de-passe-oublie');
    }
}
