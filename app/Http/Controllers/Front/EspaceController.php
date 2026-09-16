<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Point d'arrivee apres connexion.
 *
 * L'espace creatif lui-meme est le sujet de la phase 5 ; cette page tient
 * la place pour que la connexion, l'inscription et la reinitialisation
 * aient une destination reelle des maintenant.
 */
class EspaceController extends Controller
{
    public function __invoke(Request $requete): View
    {
        return view('front.espace', ['creatif' => $requete->user()]);
    }
}
