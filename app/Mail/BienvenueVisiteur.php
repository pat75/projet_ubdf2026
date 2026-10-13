<?php

namespace App\Mail;

use App\Models\Visitor;
use App\Support\Marque;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Mail d'ouverture d'un compte visiteur : lien de confirmation d'adresse, sauf adresse deja prouvee. */
class BienvenueVisiteur extends Mailable
{
    public function __construct(
        public readonly Visitor $compte,
        public readonly Marque $marque,
        public readonly ?string $lienConfirmation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Votre mémoBook est enregistré'));
    }

    public function content(): Content
    {
        // Logo et pied du gabarit commun (Marque::duMail()).
        Marque::pourMail($this->marque->code);

        return new Content(markdown: 'mail.bienvenue-visiteur');
    }
}
