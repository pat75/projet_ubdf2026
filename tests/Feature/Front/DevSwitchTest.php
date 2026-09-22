<?php

beforeEach(function () {
    config([
        'marques.marques.ub.hotes' => ['ubdf2026.ultra-book.name'],
        'marques.marques.df.hotes' => ['ubdf-dust-2026.ultra-book.name'],
    ]);

    // La suite tourne en « testing » : on se place en poste de developpement.
    app()->detectEnvironment(fn () => 'local');
});

it('propose la bascule vers Dustfolio en developpement, page conservee', function () {
    $this->get('https://ubdf2026.ultra-book.name/annuaire')
        ->assertSee('dev_only', false)
        ->assertSee('https://ubdf-dust-2026.ultra-book.name/annuaire', false);
});

it('retire le segment de langue en revenant vers Ultra-book', function () {
    $this->get('https://ubdf-dust-2026.ultra-book.name/fr/annuaire')
        ->assertSee('https://ubdf2026.ultra-book.name/annuaire', false);
});

it('n affiche jamais la bascule en production', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->get('https://ubdf2026.ultra-book.name/annuaire')
        ->assertDontSee('dev_switch_marque', false);
});
