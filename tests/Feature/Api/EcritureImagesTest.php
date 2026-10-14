<?php

use App\Models\Gallery;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->creatif = User::factory()->create(['login' => 'testapi']);
    Sanctum::actingAs($this->creatif, ['images:lire', 'images:ecrire']);
});

afterEach(function () {
    File::deleteDirectory(App\Support\DossierBook::chemin('testapi'));
});

function galerieApi(User $u, array $attrs = []): Gallery
{
    static $n = 0;
    $n++;

    return $u->galleries()->create($attrs + ['name' => 'G'.$n, 'slug' => 'g'.$n, 'status' => 'published', 'position' => $n]);
}

it('cree un portfolio nomme', function () {
    $this->postJson('/api/v1/portfolios', ['nom' => 'Mode'])
        ->assertCreated()
        ->assertJsonPath('data.nom', 'Mode');

    expect($this->creatif->galleries()->sole()->slug)->toBe('mode');
});

it('renomme, depublie et protege un portfolio', function () {
    $g = galerieApi($this->creatif);

    $this->patchJson("/api/v1/portfolios/{$g->id}", ['nom' => 'Neuf', 'publie' => false, 'mot_de_passe' => 'abcd'])
        ->assertOk()
        ->assertJsonPath('data.protege', true);

    expect($g->fresh())->name->toBe('Neuf')->status->toBe('draft')->password->toBe('abcd');

    $this->patchJson("/api/v1/portfolios/{$g->id}", ['mot_de_passe' => null]);
    expect($g->fresh()->password)->toBeNull();
});

it('ordonne les portfolios', function () {
    $a = galerieApi($this->creatif);
    $b = galerieApi($this->creatif);

    $this->putJson('/api/v1/portfolios/ordre', ['ids' => [$b->id, $a->id]])->assertNoContent();

    expect($b->fresh()->position)->toBe(1)->and($a->fresh()->position)->toBe(2);
});

it('depose une image en tete du portfolio et met a jour les compteurs', function () {
    $g = galerieApi($this->creatif);

    $this->post("/api/v1/portfolios/{$g->id}/visuels", ['fichier' => UploadedFile::fake()->image('Portrait.jpg', 1200, 800)], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('data.titre', 'Portrait');

    expect($this->creatif->fresh()->media_count)->toBe(1)
        ->and($g->fresh()->media_order)->toBe([(string) $g->media()->sole()->id]);
});

it('refuse un fichier qui n est pas une image', function () {
    $g = galerieApi($this->creatif);

    $this->post("/api/v1/portfolios/{$g->id}/visuels", ['fichier' => UploadedFile::fake()->create('x.php', 1, 'text/plain')], ['Accept' => 'application/json'])
        ->assertUnprocessable()->assertJsonValidationErrors('fichier');

    expect(Media::count())->toBe(0);
});

it('refuse un depot dans le portfolio d un autre creatif', function () {
    $autre = galerieApi(User::factory()->create());

    $this->post("/api/v1/portfolios/{$autre->id}/visuels", ['fichier' => UploadedFile::fake()->image('a.jpg')], ['Accept' => 'application/json'])
        ->assertNotFound();
});

it('modifie les textes d un visuel et le deplace dans un autre portfolio', function () {
    $depart = galerieApi($this->creatif);
    $arrivee = galerieApi($this->creatif);
    $visuel = $depart->media()->create(['user_id' => $this->creatif->id, 'filename' => 'x.jpg', 'status' => 'published']);

    $this->patchJson("/api/v1/visuels/{$visuel->id}", ['titre' => 'Robe', 'legende' => 'Soie', 'portfolio_id' => $arrivee->id])
        ->assertOk()
        ->assertJsonPath('data.titre', 'Robe')
        ->assertJsonPath('data.portfolio_id', $arrivee->id);

    expect($arrivee->fresh()->media_order)->toBe([(string) $visuel->id]);
});

it('supprime un visuel et un portfolio', function () {
    $g = galerieApi($this->creatif);
    $visuel = $g->media()->create(['user_id' => $this->creatif->id, 'filename' => 'x.jpg', 'status' => 'published', 'size' => 10]);
    $this->creatif->forceFill(['media_count' => 1, 'storage_used' => 10])->save();

    $this->deleteJson("/api/v1/visuels/{$visuel->id}")->assertNoContent();
    $this->deleteJson("/api/v1/portfolios/{$g->id}")->assertNoContent();

    expect($this->creatif->fresh()->media_count)->toBe(0)
        ->and($g->fresh()->trashed())->toBeTrue();
});

it('exige le droit images:ecrire', function () {
    Sanctum::actingAs($this->creatif, ['images:lire']);

    $this->postJson('/api/v1/portfolios', ['nom' => 'X'])->assertForbidden();
});
