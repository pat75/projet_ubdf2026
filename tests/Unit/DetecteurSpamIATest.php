<?php

use App\Services\IA\Decisions\DecisionsClient;
use App\Services\Messagerie\DetecteurSpamIA;
use Illuminate\Support\Facades\Http;

it('est actif seulement quand la config le dit', function () {
    config(['messagerie.spam_filter.active' => false]);
    expect(new DetecteurSpamIA(new DecisionsClient))->actif()->toBeFalse();

    config(['messagerie.spam_filter.active' => true]);
    expect(new DetecteurSpamIA(new DecisionsClient))->actif()->toBeTrue();
});

it('retourne la probabilite calibree renvoyee par le modele', function () {
    config(['services.openrouter.api_key' => 'test-key', 'messagerie.spam_filter.modele' => 'typesafe/jev-latest']);

    Http::fake([
        'openrouter.ai/*' => Http::response(['answers' => ['spam' => ['type' => 'noul', 'noul' => 0.93]]], 200),
    ]);

    $probabilite = (new DetecteurSpamIA(new DecisionsClient))->evaluer('Gagnez de l’argent facilement, cliquez ici.');

    expect($probabilite)->toBe(0.93);
});

it('retourne null si l appel echoue, sans jamais planter', function () {
    config(['services.openrouter.api_key' => 'test-key']);

    Http::fake(['openrouter.ai/*' => Http::response(['error' => ['message' => 'Quota depasse']], 429)]);

    expect((new DetecteurSpamIA(new DecisionsClient))->evaluer('Un message quelconque.'))->toBeNull();
});
