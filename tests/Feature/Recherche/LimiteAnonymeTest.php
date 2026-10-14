<?php

use App\Models\User;
use App\Support\LimiteAnonyme;

it('borne la liste d un anonyme a une ligne et memorise la page', function () {
    $this->get('/'); // session et requete en place
    $books = collect(range(1, 10));

    expect(LimiteAnonyme::bornee(59))->toBeTrue()
        ->and(LimiteAnonyme::premierEcran($books, 59))->toHaveCount(5)
        ->and(LimiteAnonyme::pageSuivante($books))->toBeEmpty()
        ->and(session(LimiteAnonyme::CLE_RETOUR))->not->toBeNull();
});

it('ne borne pas une liste courte', function () {
    expect(LimiteAnonyme::bornee(5))->toBeFalse()
        ->and(LimiteAnonyme::premierEcran(collect(range(1, 5)), 5))->toHaveCount(5);
});

it('laisse la liste entiere a un connecte', function () {
    $this->actingAs(User::factory()->create());
    $books = collect(range(1, 10));

    expect(LimiteAnonyme::bornee(59))->toBeFalse()
        ->and(LimiteAnonyme::premierEcran($books, 59))->toHaveCount(10)
        ->and(LimiteAnonyme::pageSuivante($books))->toHaveCount(10);
});

it('ne sert aucune page suivante a un anonyme', function () {
    $this->getJson('/recherche/cartes/1?q=illustration&recherche=mcles')
        ->assertOk()
        ->assertJson(['count' => 0, 'fin' => true]);
});

it('rend la page memorisee une seule fois apres inscription', function () {
    session([LimiteAnonyme::CLE_RETOUR => '/search?q=illustration']);

    expect(LimiteAnonyme::retour('/memobook'))->toBe('/search?q=illustration')
        ->and(LimiteAnonyme::retour('/memobook'))->toBe('/memobook');
});
