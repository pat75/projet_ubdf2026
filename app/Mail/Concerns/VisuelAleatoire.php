<?php

namespace App\Mail\Concerns;

use Illuminate\Support\Facades\Cache;

/**
 * Visuel detoure (public/_img_visuels/mail, copies reduites a 240 px)
 * pour finir un mail sur une note conviviale. Tire au hasard a chaque
 * mail, jamais deux fois de suite le meme.
 */
trait VisuelAleatoire
{
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
