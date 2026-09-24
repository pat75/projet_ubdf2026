<?php

use App\Services\IA\Decisions\DecisionsClient;
use Illuminate\Support\Facades\Http;

it('interroge l API Decisions et retourne les reponses', function () {
    config(['services.openrouter.api_key' => 'test-key']);

    Http::fake([
        'openrouter.ai/api/alpha/decisions' => Http::response([
            'model' => 'typesafe/jev-1.13-20260917',
            'answers' => ['spam' => ['type' => 'noul', 'noul' => 0.87]],
        ], 200),
    ]);

    $reponses = (new DecisionsClient)->demander('typesafe/jev-latest', 'Bonjour, voulez-vous un pret rapide ?', [
        'spam' => ['type' => 'noul', 'instructions' => 'Est-ce un spam ?'],
    ]);

    expect($reponses['spam']['noul'])->toBe(0.87);

    Http::assertSent(fn ($requete) => $requete->url() === 'https://openrouter.ai/api/alpha/decisions'
        && $requete['model'] === 'typesafe/jev-latest'
        && $requete['questions']['spam']['type'] === 'noul');
});

it('leve une exception sans cle API', function () {
    config(['services.openrouter.api_key' => null]);

    (new DecisionsClient)->demander('typesafe/jev-latest', 'texte', ['q' => ['type' => 'noul', 'instructions' => '?']]);
})->throws(RuntimeException::class, 'Clé OpenRouter non configurée.');

it('leve une exception quand l API repond en erreur', function () {
    config(['services.openrouter.api_key' => 'test-key']);

    Http::fake([
        'openrouter.ai/api/alpha/decisions' => Http::response(['error' => ['message' => 'Modele inconnu']], 404),
    ]);

    (new DecisionsClient)->demander('typesafe/jev-inexistant', 'texte', ['q' => ['type' => 'noul', 'instructions' => '?']]);
})->throws(RuntimeException::class, 'Modele inconnu');
