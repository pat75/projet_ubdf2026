<?php

namespace App\Mail;

use App\Support\Marque;
use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Models\SupportTicket;
use Filament\Facades\Filament;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Avis a l'administration : nouveau message dans un ticket du support. */
class SupportNouveauMessage extends Mailable
{
    public function __construct(public readonly SupportTicket $ticket) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Support — message de '.($this->ticket->user?->login ?? $this->ticket->email));
    }

    public function content(): Content
    {
        // Logo et pied du gabarit commun (Marque::duMail()).
        Marque::pourMail($this->ticket->brand);

        // Rendu hors requete (queue) : le panneau sert plusieurs domaines,
        // Filament exige qu'on lui designe celui de l'URL.
        Filament::currentDomain(config('ubdf.portail_domain'));

        return new Content(markdown: 'mail.support-nouveau-message', with: [
            'dernier' => $this->ticket->messages()->reorder()->latest('id')->first(),
            'lienAdmin' => SupportTicketResource::getUrl('view', ['record' => $this->ticket]),
        ]);
    }
}
