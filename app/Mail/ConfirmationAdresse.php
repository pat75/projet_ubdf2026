<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Envoye a la nouvelle adresse : elle ne remplace l'ancienne qu'une fois le lien suivi. */
class ConfirmationAdresse extends Mailable
{
    public function __construct(
        public readonly User $compte,
        public readonly string $lienConfirmation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Confirmez votre nouvelle adresse'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.confirmation-adresse');
    }
}
