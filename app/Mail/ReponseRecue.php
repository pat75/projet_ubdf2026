<?php

namespace App\Mail;

use App\Models\Conversation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Avis a l'une des parties : l'autre vient de repondre dans le fil. */
class ReponseRecue extends Mailable
{
    public function __construct(
        public readonly Conversation $conversation,
        public readonly string $lien,
        public readonly string $auteur,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Nouvelle réponse — '.$this->conversation->objet());
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.reponse-recue');
    }
}
