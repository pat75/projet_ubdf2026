<?php

use App\Models\BookSection;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->creatif = User::factory()->create();
    Sanctum::actingAs($this->creatif, ['pages:ecrire', 'memo:ecrire', 'memo:lire']);
});

it('cree une rubrique nommee, publiee d office', function () {
    $this->postJson('/api/v1/rubriques', ['titre' => 'Bio'])
        ->assertCreated()
        ->assertJsonPath('data.titre', 'Bio')
        ->assertJsonPath('data.publiee', true);
});

it('cree une page, ecrit son contenu nettoye et la publie', function () {
    $rubrique = $this->creatif->sections()->create(['kind' => BookSection::PAGES, 'title' => 'Bio', 'slug' => 'bio', 'is_published' => true, 'position' => 0]);

    $this->postJson("/api/v1/rubriques/{$rubrique->id}/pages", ['titre' => 'Parcours', 'contenu' => '<p>Ok</p><script>alert(1)</script>'])
        ->assertCreated()
        ->assertJsonPath('data.pages.0.titre', 'Parcours')
        ->assertJsonPath('data.pages.0.publiee', true);

    $page = $rubrique->articles()->sole();
    expect($page->body)->toContain('<p>Ok</p>')->not->toContain('script');
});

it('refuse de supprimer une rubrique qui a des pages', function () {
    $rubrique = $this->creatif->sections()->create(['kind' => BookSection::PAGES, 'title' => 'Bio', 'slug' => 'bio', 'is_published' => true, 'position' => 0]);
    $page = $rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'A', 'slug' => 'a', 'status' => 'draft', 'position' => 1]);

    $this->deleteJson("/api/v1/rubriques/{$rubrique->id}")->assertStatus(409);
    $this->deleteJson("/api/v1/pages/{$page->id}")->assertNoContent();
    $this->deleteJson("/api/v1/rubriques/{$rubrique->id}")->assertNoContent();
});

it('ne touche pas aux pages d un autre creatif', function () {
    $autre = User::factory()->create();
    $rubrique = $autre->sections()->create(['kind' => BookSection::PAGES, 'title' => 'X', 'slug' => 'x', 'is_published' => true, 'position' => 0]);
    $page = $rubrique->articles()->create(['user_id' => $autre->id, 'title' => 'A', 'slug' => 'a', 'status' => 'draft', 'position' => 1]);

    $this->patchJson("/api/v1/pages/{$page->id}", ['titre' => 'Pirate'])->assertNotFound();
    $this->postJson("/api/v1/rubriques/{$rubrique->id}/pages", ['titre' => 'Pirate'])->assertNotFound();
});

it('ajoute et retire un book du memo', function () {
    User::factory()->create(['login' => 'paul']);

    $this->postJson('/api/v1/memo', ['login' => 'paul'])->assertNoContent();
    $this->getJson('/api/v1/memo')->assertJsonCount(1, 'data');

    $this->deleteJson('/api/v1/memo/paul')->assertNoContent();
    $this->getJson('/api/v1/memo')->assertJsonCount(0, 'data');
});
