<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Tableau de bord de l'espace creatif : destination de la connexion, de
 * l'inscription et de la reinitialisation du mot de passe.
 */
class EspaceController extends Controller
{
    public function __invoke(Request $requete): View
    {
        $creatif = $requete->user();

        return view('espace.tableau', [
            'creatif' => $creatif,
            'chiffres' => [
                __('Galeries') => $creatif->galleries()->count(),
                __('Visuels') => $creatif->media()->count(),
                __('Pages') => $creatif->articles()->count(),
                __('Demandes reçues') => $creatif->conversations()->where('is_spam', false)->count(),
            ],
        ]);
    }
}
