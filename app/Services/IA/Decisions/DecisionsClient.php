<?php

namespace App\Services\IA\Decisions;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Client generique de l'API Decisions d'OpenRouter (POST /api/alpha/decisions) :
 * des modeles « System One » comme Jev de TypeSafe, qui ne generent pas de
 * texte mais repondent a des questions typees sur un etat donne, avec des
 * probabilites calibrees — noul (oui/non), choice (un choix parmi des
 * options), score (une position sur un bareme ordonne).
 *
 * Independant de tout domaine metier, au meme titre que
 * OpenRouterModelSelector pour le texte genere : reutilisable pour toute
 * decision structuree (routage, tri, verification...), sur ce projet comme
 * sur un autre.
 */
class DecisionsClient
{
    private const API_URL = 'https://openrouter.ai/api/alpha/decisions';

    /**
     * @param  string  $modele  Ex. « typesafe/jev-latest ».
     * @param  string|array<string, mixed>  $etat  Le texte, ou l'objet/tableau, sur lequel portent les questions.
     * @param  array<string, array<string, mixed>>  $questions  Cle arbitraire => definition
     *         (`type`: noul|choice|score, `instructions`, et selon le type `criteria`/bareme).
     * @return array<string, array<string, mixed>> Les reponses, memes cles que $questions.
     *
     * @throws RuntimeException si la cle API manque ou si l'appel echoue.
     */
    public function demander(string $modele, string|array $etat, array $questions, int $timeout = 15): array
    {
        $apiKey = config('services.openrouter.api_key');

        if (! $apiKey) {
            throw new RuntimeException('Clé OpenRouter non configurée.');
        }

        $reponse = Http::timeout($timeout)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => url('/'),
                'X-Title' => config('app.name'),
            ])
            ->post(self::API_URL, [
                'model' => $modele,
                'state' => $etat,
                'questions' => $questions,
            ]);

        if (! $reponse->successful()) {
            $erreur = $reponse->json('error.message') ?? ($reponse->body() ?: 'Erreur inconnue.');

            Log::channel('modelselector')->warning('Decisions API en echec', [
                'modele' => $modele,
                'statut' => $reponse->status(),
                'erreur' => $erreur,
            ]);

            throw new RuntimeException("Decisions API ({$modele}) : {$erreur}");
        }

        return $reponse->json('answers') ?? [];
    }
}
