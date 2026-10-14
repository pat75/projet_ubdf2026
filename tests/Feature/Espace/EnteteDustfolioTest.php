<?php

use App\Models\User;

/*
| Sur Dustfolio, la barre du haut de l'espace est celle de l'accueil :
| logo agrandi, selecteur de langue et bulle des metiers ; le burger n'y
| sert que sous 900 px (menu de l'espace en volet). Ultra-book garde la
| sienne, burger compris a toutes les tailles.
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
        ->assertSee('<div x-data class="min-[900px]:hidden">', false)
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
