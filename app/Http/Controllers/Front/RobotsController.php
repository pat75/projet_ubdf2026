<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\Marque;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * robots.txt, propre a la marque servie.
 *
 * Le legacy avait trois fichiers statiques (robots.txt, robots_df.txt,
 * robots_redirect.txt) choisis par une regle de reecriture. Celui-ci se
 * deduit de l'hote, et ferme l'espace creatif et le back-office aux
 * robots — le legacy les laissait ouverts.
 */
class RobotsController extends Controller
{
    public function __invoke(Request $requete): Response
    {
        $marque = $requete->attributes->get('marque') ?? Marque::defaut();

        $lignes = [
            'User-agent: ia_archiver',
            'Disallow: /',
            '',
            'User-agent: *',
            'Disallow: /espace',
            'Disallow: /admin',
            'Disallow: /messages/',
            'Disallow: /newsletter/desabonnement/',
            '',
            'Sitemap: '.rtrim($marque->canonique, '/').'/sitemap.xml',
        ];

        return response(implode("\n", $lignes)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
