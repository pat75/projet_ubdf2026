<?php

use App\Models\Gallery;
use App\Models\User;

it('affiche le tableau de bord du creatif connecte', function () {
    $creatif = User::factory()->create(['firstname' => 'Alice']);
    Gallery::create(['user_id' => $creatif->id, 'name' => 'Affiches', 'slug' => 'affiches', 'status' => 'published', 'position' => 1]);

    $this->actingAs($creatif)->get(route('espace'))
        ->assertOk()
        ->assertSee('Bonjour Alice')
        ->assertSee($creatif->bookUrl())
        ->assertSeeInOrder(['Galeries', '1']);
});

it('ne montre pas les rubriques non livrees dans le menu', function () {
    $this->actingAs(User::factory()->create())->get(route('espace'))
        ->assertOk()
        ->assertSee('Tableau de bord');
});
