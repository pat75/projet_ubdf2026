<?php

namespace App\Mail;

use App\Models\CoachMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

/** Message de coaching valide par un administrateur (page Coach crea). */
class CoachingMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly CoachMessage $message) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->message->objet);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.coaching', with: [
            // Espace protege : sans session, le createur passe d'abord par la connexion.
            'lienEspace' => lien('espace'),
            ...$this->visuel(),
            'lienDesabonnement' => URL::signedRoute('coach.desabonnement', ['user' => $this->message->user]),
        ]);
    }

    /**
     * Visuel detoure (public/_img_visuels/mail, copies reduites a 240 px)
     * pour finir le mail sur une note conviviale. Tire au hasard a chaque
     * mail, jamais deux fois de suite le meme.
     */
    /** @return array{visuel: ?string, hauteurVisuel: int} */
    private function visuel(): array
    {
        $fichiers = array_map('basename', glob(public_path('_img_visuels/mail/*.png')) ?: []);

        if ($fichiers === []) {
            return ['visuel' => null, 'hauteurVisuel' => 0];
        }

        $precedent = Cache::get('coach_dernier_visuel');
        $choix = collect($fichiers)->reject(fn ($f) => count($fichiers) > 1 && $f === $precedent)->random();
        Cache::forever('coach_dernier_visuel', $choix);

        // Un visuel vertical parait plus petit a hauteur egale : 40 % de plus.
        [$largeur, $hauteur] = getimagesize(public_path('_img_visuels/mail/'.$choix));

        return ['visuel' => asset('_img_visuels/mail/'.$choix), 'hauteurVisuel' => $hauteur > $largeur ? 224 : 160];
    }
}
