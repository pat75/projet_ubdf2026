<?php

namespace App\Mail;

use App\Models\User;
use App\Support\Marque;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Mail d'inscription : rappel de l'identifiant et lien de confirmation. */
class BienvenueCreatif extends Mailable
{
    public function __construct(
        public readonly User $compte,
        public readonly Marque $marque,
        public readonly string $lienConfirmation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Votre portfolio est ouvert'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.bienvenue-creatif');
    }
}
