<?php

namespace App\Http\Controllers\Espace;

use App\Http\Controllers\Controller;
use App\Services\Stats\TableauVisites;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Visites du book, sur le modele du tableau de bord analytique de
 * les-illustrateurs (/stats) : periode au choix (7, 30, 90 jours), courbe
 * des visites, repartition par surface, puis les 12 derniers mois.
 *
 * Les 90 jours sont envoyes d'un coup, par jour et par surface : le
 * changement de periode se fait dans le navigateur, sans rechargement.
 */
class StatistiquesController extends Controller
{
    public function __invoke(Request $request, TableauVisites $tableau): View
    {
        $stats = $tableau->pour($request->user());

        return view('espace.statistiques', [
            'parJour' => collect($stats['par_jour']),
            'parMois' => collect($stats['par_mois'])
                ->mapWithKeys(fn ($vues, $mois) => [Carbon::createFromFormat('!Y-m', $mois)->translatedFormat('M Y') => $vues]),
            'total' => $stats['total'],
            'surfaces' => [
                'book' => __('Book'),
                'minibook' => __('MiniBook'),
            ],
        ]);
    }
}
