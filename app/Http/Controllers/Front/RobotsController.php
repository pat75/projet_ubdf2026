<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\Robots;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * robots.txt (contenu : App\Support\Robots).
 *
 * Rendu par Laravel pour suivre la marque de l'hote : le serveur web ne
 * doit plus servir de fichier statique a cette adresse.
 *
 * Le legacy avait trois fichiers statiques (robots.txt, robots_df.txt,
 * robots_redirect.txt) choisis par une regle de reecriture. Celui-ci
 * ferme l'espace creatif et le back-office aux robots — le legacy les
 * laissait ouverts.
 */
class RobotsController extends Controller
{
    public function __invoke(Request $requete): Response
    {
        return response(Robots::contenu(Robots::marqueDeHote($requete->getHost())), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
