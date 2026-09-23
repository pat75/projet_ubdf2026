<?php

use App\Models\User;

/*
 | Le menu de droite, repris de l'espace d'origine. Ces tests ne disent
 | rien de l'aspect — ils fixent la structure : les deux listes, l'ordre
 | des entrees, l'etat de la formule et la rubrique ouverte.
 */

it('presente les deux listes du menu, le compte puis le book', function () {
    $creatif = User::factory()->create(['plan' => 0]);

    $this->actingAs($creatif)->get(route('espace'))
        ->assertOk()
        ->assertSeeInOrder([
            'Mon compte',
            'Tableau de bord',
            'Ma formule',
            'Mes messages',
            'Mon portfolio',
            'Configurer',
            'Modifier',
            'Diffuser',
            'Contenu du portfolio',
            'Images',
            'Pages',
        ]);
});

it('annonce une formule gratuite ou payante', function () {
    $gratuit = User::factory()->create(['plan' => 0]);

    $this->actingAs($gratuit)->get(route('espace'))->assertOk()->assertSee('Formule gratuite');

    $payant = User::factory()->create(['plan' => 1, 'plan_started_at' => now(), 'plan_months' => 12]);

    $this->actingAs($payant)->get(route('espace'))->assertOk()->assertDontSee('Formule gratuite');
});

it('montre le fanion de selection quand le book est en page d accueil', function () {
    $creatif = User::factory()->create(['in_home_selection' => true]);

    $this->actingAs($creatif)->get(route('espace'))->assertOk()->assertSee('Sélection');

    $creatif->update(['in_home_selection' => false]);

    $this->actingAs($creatif)->get(route('espace'))->assertOk()->assertDontSee('Sélection');
});

it('marque la rubrique ouverte', function () {
    $creatif = User::factory()->create();

    // La page courante porte `aria-current`, et elle seule.
    $reponse = $this->actingAs($creatif)->get(route('espace.compte'))->assertOk();

    expect(substr_count($reponse->getContent(), 'aria-current="page"'))->toBe(1);
});

it('donne le lien du book et la deconnexion', function () {
    $creatif = User::factory()->create();

    $this->actingAs($creatif)->get(route('espace'))
        ->assertOk()
        ->assertSee($creatif->bookUrl())
        ->assertSee('Voir mon book')
        ->assertSee('Déconnexion');
});

it('n embarque plus de bascule sombre', function () {
    $this->actingAs(User::factory()->create())->get(route('espace'))
        ->assertOk()
        ->assertDontSee('espace_dark');
});
