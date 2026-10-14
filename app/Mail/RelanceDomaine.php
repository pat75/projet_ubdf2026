<?php

namespace App\Mail;

use App\Models\Domaine;
use App\Support\Marque;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Relance avant l'echeance d'un nom de domaine (J-30, J-15, J-5). */
class RelanceDomaine extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Domaine $domaine,
        public readonly Marque $marque,
        public readonly int $joursRestants,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Votre nom de domaine :nom expire dans :n jours', ['nom' => $this->domaine->nom, 'n' => $this->joursRestants]));
    }

    public function content(): Content
    {
        Marque::pourMail($this->marque->code);

        // Envoi planifie : domaine canonique de la marque, pas l'hote courant.
        return new Content(view: 'mail.relance-domaine', text: 'mail.relance-domaine-texte', with: [
            'lienDomaine' => rtrim($this->marque->canonique, '/')
                .($this->marque->multilingue() ? '/'.$this->marque->locale() : '')
                .'/espace/domaine',
            'echeance' => $this->domaine->expire_le?->translatedFormat('j F Y'),
        ]);
    }
}
