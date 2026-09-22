<?php

namespace App\View\Components\Dev;

use App\Support\Marque;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Bascule Ultra-book <-> Dustfolio, en developpement seulement.
 *
 * Conserve la page courante : `/fr/annuaire` sur Dustfolio mene a
 * `/annuaire` sur Ultra-book ; dans l'autre sens, l'URL sans langue est
 * redirigee par ResoudreLangue vers la langue du visiteur.
 */
class SwitchMarque extends Component
{
    public function shouldRender(): bool
    {
        return app()->environment(['local', 'development']);
    }

    public function render(): View
    {
        $courante = request()->attributes->get('marque') ?? Marque::defaut();
        $cible = Marque::depuisCode($courante->estDefaut() ? 'df' : config('marques.defaut'));

        $chemin = '/'.ltrim(request()->path(), '/');

        if ($courante->multilingue()) {
            $langues = implode('|', array_map('preg_quote', $courante->langues));
            $chemin = preg_replace('#^/('.$langues.')(?=/|$)#', '', $chemin) ?: '/';
        }

        $hote = config("marques.marques.{$cible->code}.hotes")[0] ?? null;
        $query = request()->getQueryString();

        return view('components.dev.switch-marque', [
            'courante' => $courante,
            'cible' => $cible,
            'url' => $hote ? 'https://'.$hote.$chemin.($query ? '?'.$query : '') : null,
        ]);
    }
}
