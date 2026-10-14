<?php

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

function portfolioApi(User $u, string $nom, int $position, array $attrs = [])
{
    return $u->galleries()->create($attrs + ['name' => $nom, 'slug' => Str::slug($nom), 'status' => 'published', 'position' => $position]);
}

beforeEach(function () {
    $this->creatif = User::factory()->create(['login' => 'jeanne']);
});

it('liste les portfolios dans l ordre, avec leur nombre de visuels', function () {
    $b = portfolioApi($this->creatif, 'B', 2);
    portfolioApi($this->creatif, 'A', 1);
    $b->media()->create(['user_id' => $this->creatif->id, 'filename' => 'x.jpg', 'status' => 'published']);
    portfolioApi(User::factory()->create(), 'Autre', 0);
    Sanctum::actingAs($this->creatif, ['images:lire']);

    $this->getJson('/api/v1/portfolios')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.nom', 'A')
        ->assertJsonPath('data.1.nb_visuels', 1);
});

it('rend un portfolio et ses visuels dans l ordre du book', function () {
    $g = portfolioApi($this->creatif, 'A', 1);
    $v1 = $g->media()->create(['user_id' => $this->creatif->id, 'filename' => 'un.jpg', 'title' => 'Un', 'status' => 'published']);
    $v2 = $g->media()->create(['user_id' => $this->creatif->id, 'filename' => 'deux.jpg', 'title' => 'Deux', 'status' => 'published']);
    $g->update(['media_order' => [(string) $v2->id, (string) $v1->id]]);
    Sanctum::actingAs($this->creatif, ['images:lire']);

    $this->getJson("/api/v1/portfolios/{$g->id}")
        ->assertOk()
        ->assertJsonPath('data.visuels.0.titre', 'Deux')
        ->assertJsonPath('data.visuels.1.titre', 'Un')
        ->assertJsonStructure(['data' => ['visuels' => [['id', 'url', 'miniature', 'largeur', 'hauteur']]]])
        ->assertJsonMissingPath('data.mot_de_passe');
});

it('ne montre pas le portfolio d un autre creatif', function () {
    $autre = portfolioApi(User::factory()->create(), 'Autre', 1);
    Sanctum::actingAs($this->creatif, ['images:lire']);

    $this->getJson("/api/v1/portfolios/{$autre->id}")->assertNotFound();
});

it('exige le droit images:lire', function () {
    Sanctum::actingAs($this->creatif, ['messages:lire']);

    $this->getJson('/api/v1/portfolios')->assertForbidden();
});
