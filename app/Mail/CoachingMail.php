<?php

namespace App\Mail;

use App\Support\Marque;
use App\Mail\Concerns\VisuelAleatoire;
use App\Models\CoachMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;

/** Message de coaching valide par un administrateur (page Coach crea). */
class CoachingMail extends Mailable implements ShouldQueue
{
    use Queueable, VisuelAleatoire;

    public function __construct(public readonly CoachMessage $message) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->message->objet);
    }

    public function content(): Content
    {
        // Logo et pied du gabarit commun (Marque::duMail()).
        Marque::pourMail($this->message->user?->brand);

        return new Content(markdown: 'mail.coaching', with: [
            // Espace protege : sans session, le createur passe d'abord par la connexion.
            'lienEspace' => lien('espace'),
            ...$this->visuel(),
            'lienDesabonnement' => URL::signedRoute('coach.desabonnement', ['user' => $this->message->user]),
        ]);
    }
}
