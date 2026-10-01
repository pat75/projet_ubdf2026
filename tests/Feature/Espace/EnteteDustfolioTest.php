<?php

use App\Models\User;

/*
| Sur Dustfolio, la barre du haut de l'espace est celle de l'accueil :
| ni burger, logo agrandi, selecteur de langue et bulle des metiers.
| Ultra-book garde la sienne.
*/

beforeEach(function () {
    config([
        'marques.marques.ub.hotes' => ['ubdf2026.ultra-book.name'],
        'marques.marques.df.hotes' => ['ubdf-dust-2026.ultra-book.name'],
    ]);
    $this->actingAs(User::factory()->create(['brand' => 'df']));
});

it('donne a l espace Dustfolio la barre de l accueil', function () {
    $this->get('https://ubdf-dust-2026.ultra-book.name/en/espace')
        ->assertOk()
        ->assertDontSee('leading-none text-black" aria-label="Menu">☰', false)
        ->assertSee('selecteur_langue', false)
        ->assertSee('Categories filter')
        ->assertSee('w-[155px] min-[900px]:w-[168px]', false);
});

it('laisse a l espace Ultra-book sa barre', function () {
    $this->actingAs(User::factory()->create(['brand' => 'ub']))
        ->get('https://ubdf2026.ultra-book.name/espace')
        ->assertOk()
        ->assertSee('leading-none text-black" aria-label="Menu">☰', false)
        ->assertDontSee('selecteur_langue', false)
        ->assertSee('w-[84px] min-[900px]:w-[120px]', false);
});
