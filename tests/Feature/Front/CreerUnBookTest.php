<?php

beforeEach(function () {
    config([
        'marques.marques.ub.hotes' => ['ubdf2026.ultra-book.name'],
        'marques.marques.df.hotes' => ['ubdf-dust-2026.ultra-book.name'],
    ]);
});

it('sert la page Creer un book sur Ultra-book, en francais', function () {
    $this->get('https://ubdf2026.ultra-book.name/creer-un-book')
        ->assertOk()
        ->assertSee('Créer mon book')
        ->assertSee('/img_admin/diffusion-b.svg', false)
        ->assertSee('id="inscription_classic"', false)
        ->assertSee('name="captcha"', false);
});

it('pointe le menu vers la page, plus vers une fenetre', function () {
    $this->get('https://ubdf2026.ultra-book.name/')
        ->assertOk()
        ->assertSee('href="https://ubdf2026.ultra-book.name/creer-un-book"', false)
        ->assertDontSee("ouvrir('creerbook')", false);
});

it('sert la page Dustfolio sous un segment traduit', function () {
    $this->get('https://ubdf-dust-2026.ultra-book.name/en/create-a-book')->assertOk();
    $this->get('https://ubdf-dust-2026.ultra-book.name/fr/creer-un-book')->assertOk()->assertSee('Créer mon book');
});

it('redirige le segment d une autre langue vers celui de la langue', function () {
    $this->get('https://ubdf-dust-2026.ultra-book.name/en/creer-un-book')
        ->assertRedirect('https://ubdf-dust-2026.ultra-book.name/en/create-a-book');
    $this->get('https://ubdf2026.ultra-book.name/create-a-book')
        ->assertRedirect('https://ubdf2026.ultra-book.name/creer-un-book');
});

it('n affiche ni le menu du haut ni le pied de page', function () {
    $this->get('https://ubdf2026.ultra-book.name/creer-un-book')
        ->assertOk()
        ->assertDontSee('id="menu-top-fixed"', false)
        ->assertDontSee('id="menu-top-fixed-mobile"', false);
});
