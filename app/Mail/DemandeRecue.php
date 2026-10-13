<?php

namespace App\Mail;

use App\Support\Marque;
use App\Models\Conversation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Avis au creatif : une demande vient d'arriver sur son book. */
class DemandeRecue extends Mailable
{
    public function __construct(
        public readonly Conversation $conversation,
        public readonly string $lien,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->conversation->objet().' — '.$this->conversation->sender_name,
            // L'adresse du visiteur n'est jamais exposee : c'est tout l'objet
            // de l'intermediation. La reponse passe par le lien du fil.
            replyTo: [config('mail.from.address')],
        );
    }

    public function content(): Content
    {
        // Logo et pied du gabarit commun (Marque::duMail()).
        Marque::pourMail($this->conversation->user?->brand);

        return new Content(markdown: 'mail.demande-recue');
    }
}
