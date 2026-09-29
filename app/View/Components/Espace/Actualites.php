<?php

namespace App\View\Components\Espace;

use App\Models\Actualite;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

/**
 * Le bloc d'actualites d'un tableau de bord.
 *
 * `<x-espace.actualites emplacement="creatif" />` : le composant va
 * chercher lui-meme les actualites actives de cet emplacement, les
 * controleurs n'ont rien a passer.
 */
class Actualites extends Component
{
    /** @var Collection<int, Actualite> */
    public Collection $actualites;

    public function __construct(public string $emplacement = 'creatif')
    {
        $this->actualites = Actualite::query()
            ->actif()
            ->pour($emplacement)
            ->ordonne()
            ->get();
    }

    /** L'adresse d'un visuel, qu'il soit depose au back-office ou deja dans `public`. */
    public static function urlVisuel(string $chemin): string
    {
        if (str_starts_with($chemin, 'http') || str_starts_with($chemin, '/')) {
            return $chemin;
        }

        return asset('storage/'.ltrim($chemin, '/'));
    }

    public function render(): View
    {
        return view('components.espace.actualites');
    }
}
