<?php

namespace App\Livewire\Visiteur;

use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Component;

/**
 * « Mes dernieres visites » du tableau de bord visiteur : books consultes,
 * regroupes par mois, 30 a la fois, « Charger plus » pour les anterieurs.
 */
class DernieresVisites extends Component
{
    public const PAR_PAGE = 30;

    public int $limite = self::PAR_PAGE;

    public function chargerPlus(): void
    {
        $this->limite += self::PAR_PAGE;
    }

    public function render(): View
    {
        // Une de plus que la limite : dit s'il reste des visites a charger.
        $visites = auth('visitor')->user()->visites()->with('category')->limit($this->limite + 1)->get();

        return view('livewire.visiteur.dernieres-visites', [
            'parMois' => $visites->take($this->limite)
                ->groupBy(fn ($book) => Carbon::parse($book->pivot->visited_at)->format('Y-m')),
            'reste' => $visites->count() > $this->limite,
        ]);
    }
}
