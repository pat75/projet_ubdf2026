<?php

namespace App\Mail;

use App\Models\User;
use App\Support\Marque;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Envoi au createur du mot de passe qu'un administrateur vient de tirer
 * pour lui, depuis sa fiche au back-office.
 *
 * Le mot de passe voyage en clair dans le message : c'est le prix de
 * l'assistance par e-mail, et la raison pour laquelle il est provisoire et
 * a changer a la premiere connexion.
 */
class NouveauMotDePasse extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User $creatif,
        public readonly Marque $marque,
        public readonly string $motDePasse,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Votre nouveau mot de passe :marque', ['marque' => $this->marque->nom]));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.nouveau-mot-de-passe', with: [
            'lienConnexion' => rtrim($this->marque->canonique, '/')
                .($this->marque->multilingue() ? '/'.$this->marque->locale() : '')
                .'/connexion',
        ]);
    }
}
