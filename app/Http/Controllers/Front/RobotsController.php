<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\Robots;
use Illuminate\Http\Response;

/**
 * robots.txt (contenu : App\Support\Robots).
 *
 * En pratique le serveur web sert le fichier public/robots.txt, ecrit par
 * la commande planifiee `ubdf:robots` ; cette route reste pour un serveur
 * qui lui passerait la main.
 *
 * Le legacy avait trois fichiers statiques (robots.txt, robots_df.txt,
 * robots_redirect.txt) choisis par une regle de reecriture. Celui-ci
 * ferme l'espace creatif et le back-office aux robots — le legacy les
 * laissait ouverts.
 */
class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        return response(Robots::contenu(), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
