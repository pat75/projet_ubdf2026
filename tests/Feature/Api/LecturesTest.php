<?php

use App\Models\BookSection;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->creatif = User::factory()->create(['login' => 'jeanne', 'city' => 'Lyon', 'admin_note' => 'secret admin']);
});

it('rend le compte complet sans les champs internes', function () {
    Sanctum::actingAs($this->creatif, ['compte:lire']);

    $this->getJson('/api/v1/moi')
        ->assertOk()
        ->assertJsonPath('data.ville', 'Lyon')
        ->assertJsonMissingPath('data.admin_note')
        ->assertJsonMissingPath('data.signup_ip');
});

it('rend les rubriques et leurs pages dans l ordre du book', function () {
    $rubrique = $this->creatif->sections()->create(['kind' => BookSection::PAGES, 'title' => 'Bio', 'slug' => 'bio', 'is_published' => true, 'position' => 0]);
    $a = $rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'A', 'slug' => 'a', 'status' => 'published', 'position' => 1, 'body' => '<p>A</p>']);
    $b = $rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'B', 'slug' => 'b', 'status' => 'draft', 'position' => 2]);
    $rubrique->update(['page_order' => [(string) $b->id, (string) $a->id]]);
    Sanctum::actingAs($this->creatif, ['pages:lire']);

    $this->getJson('/api/v1/pages')
        ->assertOk()
        ->assertJsonPath('data.0.titre', 'Bio')
        ->assertJsonPath('data.0.pages.0.titre', 'B')
        ->assertJsonPath('data.0.pages.1.contenu', '<p>A</p>');
});

it('liste les books du memo, filtres par la recherche', function () {
    $paul = User::factory()->create(['login' => 'paul', 'firstname' => 'Paul']);
    $zoe = User::factory()->create(['login' => 'zoe', 'firstname' => 'Zoe']);
    $this->creatif->memoBooks()->create(['book_id' => $paul->id]);
    $this->creatif->memoBooks()->create(['book_id' => $zoe->id]);
    Sanctum::actingAs($this->creatif, ['memo:lire']);

    $this->getJson('/api/v1/memo')->assertOk()->assertJsonCount(2, 'data');
    $this->getJson('/api/v1/memo?recherche=zoe')->assertJsonCount(1, 'data')->assertJsonPath('data.0.login', 'zoe');
});

it('rend les statistiques sur 90 jours et 12 mois', function () {
    Sanctum::actingAs($this->creatif, ['stats:lire']);

    $reponse = $this->getJson('/api/v1/statistiques')->assertOk()->assertJsonStructure(['data' => ['par_jour', 'par_mois', 'total']]);

    expect($reponse->json('data.par_jour'))->toHaveCount(90)
        ->and($reponse->json('data.par_mois'))->toHaveKey(now()->format('Y-m'));
});

it('exige le droit de chaque lecture', function (string $route) {
    Sanctum::actingAs($this->creatif, ['images:lire']);

    $this->getJson($route)->assertForbidden();
})->with(['/api/v1/pages', '/api/v1/memo', '/api/v1/statistiques']);

it('modifie les champs du compte autorises', function () {
    Sanctum::actingAs($this->creatif, ['compte:ecrire']);

    $this->patchJson('/api/v1/moi', ['ville' => 'Paris', 'societe' => '  ', 'accepte_sms' => true])
        ->assertOk()
        ->assertJsonPath('data.ville', 'Paris');

    expect($this->creatif->fresh())->city->toBe('Paris')->company->toBeNull()->accepts_sms->toBeTrue();
});

it('ignore l adresse mail, le mot de passe et les champs internes', function () {
    Sanctum::actingAs($this->creatif, ['compte:ecrire']);
    $mail = $this->creatif->email;

    $this->patchJson('/api/v1/moi', ['email' => 'pirate@x.fr', 'password' => 'xxxxxxxx', 'admin_note' => 'x', 'plan' => 'pro'])->assertOk();

    expect($this->creatif->fresh())->email->toBe($mail)->admin_note->toBe('secret admin');
});

it('valide le site comme une adresse web', function () {
    Sanctum::actingAs($this->creatif, ['compte:ecrire']);

    $this->patchJson('/api/v1/moi', ['site' => 'javascript:alert(1)'])->assertUnprocessable()->assertJsonValidationErrors('site');
});
