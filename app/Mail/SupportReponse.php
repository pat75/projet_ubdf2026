<?php

namespace App\Mail;

use App\Support\Marque;
use App\Mail\Concerns\VisuelAleatoire;
use App\Models\SupportTicket;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Reponse du support au creatif ou au visiteur, avec le lien du fil. */
class SupportReponse extends Mailable
{
    use VisuelAleatoire;

    public function __construct(public readonly SupportTicket $ticket) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Réponse du support '.Marque::depuisCode($this->ticket->brand)->nom);
    }

    public function content(): Content
    {
        // Logo et pied du gabarit commun (Marque::duMail()).
        Marque::pourMail($this->ticket->brand);

        return new Content(markdown: 'mail.support-reponse', with: [
            'reponse' => $this->ticket->messages()->reorder()->latest('id')->first(),
            'lien' => $this->ticket->lien(),
            ...$this->visuel(),
        ]);
    }
}
