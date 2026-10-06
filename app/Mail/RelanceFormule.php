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
 * Relance d'abonnement (abo_relance_liste_2026.php du legacy, lance a la
 * main depuis une URL d'administration).
 */
class RelanceFormule extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User $creatif,
        public readonly Marque $marque,
        /** Jours restants avant l'echeance : 5, 0 le jour meme, ou -1 si elle est deja passee. */
        public readonly int $joursRestants,
    ) {}

    public function envelope(): Envelope
    {
        if ($this->joursRestants < 0) {
            return new Envelope(subject: __('Votre formule :marque est arrivée à échéance', ['marque' => $this->marque->nom]));
        }

        return new Envelope(subject: $this->joursRestants > 0
            ? __('Votre formule :marque arrive à échéance dans :n jours', ['marque' => $this->marque->nom, 'n' => $this->joursRestants])
            : __('Votre formule :marque arrive à échéance aujourd’hui', ['marque' => $this->marque->nom]));
    }

    public function content(): Content
    {
        // Hors requete (envoi planifie), on part du domaine canonique de la
        // marque du createur, pas de l'hote courant.
        return new Content(view: 'mail.relance-formule', text: 'mail.relance-formule-texte', with: [
            'lienFormule' => rtrim($this->marque->canonique, '/')
                .($this->marque->multilingue() ? '/'.$this->marque->locale() : '')
                .'/espace/formule',
        ]);
    }
}
