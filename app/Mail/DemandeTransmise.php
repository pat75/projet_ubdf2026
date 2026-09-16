<?php

namespace App\Mail;

use App\Models\Conversation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Accuse de reception au visiteur, avec son lien de suivi. */
class DemandeTransmise extends Mailable
{
    public function __construct(
        public readonly Conversation $conversation,
        public readonly string $lien,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre demande a été transmise');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.demande-transmise');
    }
}
