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
            'Diffuser',
            'Statistiques',
            'Contenu du portfolio',
            'Images',
            'Pages',
        ]);
});

it('ne distingue la formule payante que par sa pastille', function () {
    // La maquette ne dit plus rien de la formule gratuite : c'est
    // l'absence de pastille qui la signale.
    $gratuit = User::factory()->create(['plan' => 0]);

    $this->actingAs($gratuit)->get(route('espace'))->assertOk()->assertDontSee('★ Formule', escape: false);

    $payant = User::factory()->create(['plan' => 1, 'plan_started_at' => now(), 'plan_months' => 12]);

    $this->actingAs($payant)->get(route('espace'))->assertOk()->assertSee('★ Formule', escape: false);
});

it('montre le fanion de selection quand le book est en page d accueil', function () {
    $creatif = User::factory()->create(['in_home_selection' => true]);

    // « ★ » : le menu plein ecran du portail contient « Sélectionnez… ».
    $this->actingAs($creatif)->get(route('espace'))->assertOk()->assertSee('★ Sélection', escape: false);

    $creatif->update(['in_home_selection' => false]);

    $this->actingAs($creatif)->get(route('espace'))->assertOk()->assertDontSee('★ Sélection', escape: false);
});

it('ne propose pas de creer un portfolio dans le menu plein ecran', function () {
    $creatif = User::factory()->create();

    $this->actingAs($creatif)->get(route('espace'))->assertOk()->assertDontSee('Créer un portfolio');
});

it('marque la rubrique ouverte', function () {
    $creatif = User::factory()->create();

    // La page courante porte `aria-current`, et elle seule. Le menu est
    // rendu deux fois : colonne de droite (desktop) et volet « Plus »
    // (mobile) ; « Mon compte » n'a pas d'onglet dans la barre du bas.
    $reponse = $this->actingAs($creatif)->get(route('espace.compte'))->assertOk();

    expect(substr_count($reponse->getContent(), 'aria-current="page"'))->toBe(2);
});

it('allume l onglet mobile de la rubrique ouverte', function () {
    $creatif = User::factory()->create();

    // Menu desktop, volet mobile et onglet « Messages » de la barre du bas.
    $reponse = $this->actingAs($creatif)->get(route('espace.messages'))->assertOk();

    expect(substr_count($reponse->getContent(), 'aria-current="page"'))->toBe(3);
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
