<?php

namespace App\Mail;

use App\Support\Marque;
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
        return new Envelope(subject: 'Nouvelle réponse de '.$this->auteur.' — '.$this->conversation->objet());
    }

    public function content(): Content
    {
        // Logo et pied du gabarit commun (Marque::duMail()).
        Marque::pourMail($this->conversation->user?->brand);

        return new Content(markdown: 'mail.reponse-recue');
    }
}
