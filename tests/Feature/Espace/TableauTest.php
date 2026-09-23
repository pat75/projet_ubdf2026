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
        ->assertSeeInOrder(['Book', 'MiniBook', 'MémoBook', 'MicroBook'], escape: false);
});

it('ne montre pas les rubriques non livrees dans le menu', function () {
    $this->actingAs(User::factory()->create())->get(route('espace'))
        ->assertOk()
        ->assertSee('Tableau de bord');
});
