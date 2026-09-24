<?php

use App\Models\User;

/*
 | La barre de tete est la meme partout : celle de l'accueil. Connecte, le
 | createur y remplace « Connexion » et « Créer un book » par sa vignette,
 | son nom et son metier — sur le portail comme dans son espace.
 */

it('montre les boutons de connexion a un visiteur', function () {
    $this->get(route('home'))->assertOk()
        ->assertSee('btn_connection', false)
        ->assertSee('Créer un book')
        ->assertDontSee('barre_createur', false);
});

it('montre le createur connecte a la place des boutons, sur le portail', function () {
    $creatif = User::factory()->create(['firstname' => 'Adolie', 'lastname' => 'Day']);

    $this->actingAs($creatif)->get(route('home'))->assertOk()
        ->assertSee('barre_createur', false)
        ->assertSee('Adolie Day')
        // Plus de bouton « Connexion » dans la barre (le bouton du menu
        // lateral et ceux de l'accueil, eux, restent).
        ->assertDontSee('ui black basic button btn_connection', false);
});

it('montre le meme bloc createur dans l espace', function () {
    $creatif = User::factory()->create(['firstname' => 'Adolie', 'lastname' => 'Day']);

    $this->actingAs($creatif)->get(route('espace.formule'))->assertOk()
        ->assertSee('barre_createur', false)
        ->assertSee('Adolie Day');
});
