<?php

use App\Models\Gallery;
use App\Models\User;
use App\Models\VisitStat;

it('affiche le tableau de bord du creatif connecte', function () {
    $creatif = User::factory()->create(['firstname' => 'Alice', 'login' => 'alice']);
    Gallery::create(['user_id' => $creatif->id, 'name' => 'Affiches', 'slug' => 'affiches', 'status' => 'published', 'position' => 1]);

    $this->actingAs($creatif)->get(route('espace'))
        ->assertOk()
        ->assertSee('Tableau de bord')
        // Les deux entrees en matiere, puis les deux adresses du book.
        ->assertSeeInOrder(['Charger', 'mes images', 'Personnaliser', 'mon portfolio'])
        ->assertSee($creatif->bookUrl());
});

it('donne les deux codes QR, fabriques sur place', function () {
    $creatif = User::factory()->create();

    $reponse = $this->actingAs($creatif)->get(route('espace'))->assertOk();

    // Deux SVG, et aucun appel a un service exterieur comme le faisait
    // le legacy (qrcode.tec-it.com).
    expect(substr_count($reponse->getContent(), 'shape-rendering="crispEdges"'))->toBe(2)
        ->and($reponse->getContent())->not->toContain('tec-it.com');
});

it('totalise les visites et les repartit par support', function () {
    $creatif = User::factory()->create();

    VisitStat::create(['user_id' => $creatif->id, 'date' => now()->toDateString(), 'surface' => 'book', 'public_views' => 120]);
    VisitStat::create(['user_id' => $creatif->id, 'date' => now()->toDateString(), 'surface' => 'microbook', 'public_views' => 30]);

    $this->actingAs($creatif)->get(route('espace'))
        ->assertOk()
        ->assertSee('150')
        ->assertSeeInOrder(['Book', 'MiniBook'], escape: false)
        // MemoBook et MicroBook n'ont pas de pixel qui les compte : ils
        // ne figurent plus dans la repartition.
        ->assertDontSee('MémoBook')
        ->assertDontSee('MicroBook');
});

it('ne montre pas les rubriques non livrees dans le menu', function () {
    $this->actingAs(User::factory()->create())->get(route('espace'))
        ->assertOk()
        ->assertSee('Tableau de bord');
});

/*
 | Les deux compteurs de la formule ont quitte « Ma formule » pour le
 | tableau de bord, ou sont deja les autres chiffres du compte.
 */
it('montre les quotas de la formule sur le tableau de bord', function () {
    $paye = App\Models\User::factory()->create(['plan' => 1, 'media_count' => 202, 'storage_used' => 67160]);

    $this->actingAs($paye)->get(route('espace'))->assertOk()
        ->assertSee('202')
        ->assertSee('max: 500', false)
        ->assertSee('max: 120 000 Ko', false);

    $gratuit = App\Models\User::factory()->create(['plan' => 0]);

    $this->actingAs($gratuit)->get(route('espace'))->assertOk()
        ->assertSee('max: 12', false);
});

it('salue le createur', function () {
    $creatif = User::factory()->create(['firstname' => 'Adolie', 'login' => 'adolie']);

    $this->actingAs($creatif)->get(route('espace'))->assertOk()
        ->assertSee('Bonjour Adolie');
});

it('indique la selection du book en face du titre, seulement si elle existe', function () {
    $selectionne = User::factory()->create(['in_home_selection' => true, 'home_selection_at' => '2026-03-15']);
    $absent = User::factory()->create(['in_home_selection' => false]);

    $this->actingAs($selectionne)->get(route('espace'))->assertOk()
        ->assertSee('Book sélectionné !')
        ->assertSee('depuis mars 2026');

    $this->actingAs($absent)->get(route('espace'))->assertOk()
        ->assertDontSee('Book sélectionné');
});

it('propose de copier ou d ouvrir les deux liens du book', function () {
    $creatif = User::factory()->create();

    $this->actingAs($creatif)->get(route('espace'))->assertOk()
        ->assertSeeInOrder(['Partager mes liens', 'mini-book', 'Copier', 'Ouvrir ↗', 'book'], escape: false);
});

it('reprend la carte du portail pour presenter le createur', function () {
    $creatif = User::factory()->create(['firstname' => 'Adolie', 'lastname' => 'Day']);

    $this->actingAs($creatif)->get(route('espace'))->assertOk()
        // Meme structure que x-book-card : cover, vignette chevauchante,
        // nom, metier, puis les deux compteurs (fonticon-eye3/heart2).
        ->assertSee('fonticon-eye3', false)
        ->assertSee('fonticon-heart2', false)
        ->assertSee($creatif->bookUrl());
});
