<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\JetonRequest;
use App\Services\Api\ConnexionParCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Connexion d'une application desktop par code a valider sur le site (voir ConnexionParCode). */
class AppareilController extends Controller
{
    public function code(Request $requete, ConnexionParCode $connexion): JsonResponse
    {
        $donnees = $requete->validate([
            'appareil' => ['required', 'string', 'max:100'],
            'droits' => ['sometimes', 'array', 'min:1'],
            'droits.*' => ['string', Rule::in(JetonRequest::DROITS)],
        ]);

        return response()->json(
            $connexion->demarrer($donnees['appareil'], array_values(array_unique($donnees['droits'] ?? JetonRequest::DROITS))) + [
                'url' => route('espace.compte').'#appareils',
            ],
            201
        );
    }

    /** Interroge en boucle (toutes les 5 s) : 202 tant que le creatif n'a pas valide. */
    public function jeton(Request $requete, ConnexionParCode $connexion): JsonResponse
    {
        $resultat = $connexion->echanger($requete->validate(['code_appareil' => ['required', 'string', 'size:64']])['code_appareil']);

        return match ($resultat) {
            'en_attente' => response()->json(['statut' => 'en_attente'], 202),
            'expire' => response()->json(['statut' => 'expire'], 410),
            default => response()->json($resultat, 201),
        };
    }
}
