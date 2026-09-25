<?php

use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

beforeEach(function () {
    $this->login = 'creatif-test';
    $this->dossier = App\Support\DossierBook::chemin($this->login);
    File::ensureDirectoryExists($this->dossier);

    app(ImageManager::class)->createImage(800, 600)->save($this->dossier.'/visuel.jpg');
});

afterEach(function () {
    File::deleteDirectory($this->dossier);
});

function tailleReponse($reponse): string
{
    $fichier = tempnam(sys_get_temp_dir(), 'ubdf');
    file_put_contents($fichier, $reponse->streamedContent() ?: $reponse->getContent());
    $taille = getimagesize($fichier);
    unlink($fichier);

    return $taille[0].'x'.$taille[1];
}

it('sert la declinaison demandee', function () {
    $reponse = $this->get("/books/{$this->login}/front_desk/visuel.jpg");

    $reponse->assertOk();
    expect(tailleReponse($reponse))->toBe('250x136');
});

it('ne permute pas les segments de l URL', function () {
    // Laravel passe les parametres scalaires dans l'ordre de l'URI, pas par
    // leur nom : une signature mal ordonnee lisait la declinaison comme un
    // nom de fichier. Rien ne le montrait hors d'une requete reelle.
    expect(tailleReponse($this->get("/books/{$this->login}/ptf_small/visuel.jpg")))->toBe('48x36')
        ->and(tailleReponse($this->get("/books/{$this->login}/iph_small/visuel.jpg")))->toBe('75x75');
});

it('applique la declinaison par defaut sans segment', function () {
    $reponse = $this->get("/books/{$this->login}/visuel.jpg");

    $reponse->assertOk();
    // `ptf_medium` : boite de 550 de large, source de 800.
    expect(tailleReponse($reponse))->toBe('550x413');
});

it('refuse une declinaison qui n est pas declaree', function () {
    // phpThumb acceptait ses dimensions depuis l'URL.
    $this->get("/books/{$this->login}/900x900/visuel.jpg")->assertNotFound();
    $this->get("/books/{$this->login}/inventee/visuel.jpg")->assertNotFound();
});

it('rend l image par defaut quand le fichier a disparu', function () {
    // 23 % des lignes de la table source n'ont pas de fichier sur le disque.
    $this->get("/books/{$this->login}/front_desk/absent.jpg")
        ->assertOk()
        ->assertHeader('content-type', 'image/gif');
});

it('ne laisse pas remonter l arborescence', function () {
    $this->get("/books/{$this->login}/front_desk/..%2F..%2Fdatabase.sqlite")->assertNotFound();
});

it('met en cache longuement une declinaison produite', function () {
    $this->get("/books/{$this->login}/front_desk/visuel.jpg")
        ->assertHeader('cache-control', 'immutable, max-age=31536000, public');
});

it('donne aux vignettes du portail la declinaison des cartes', function () {
    $creatif = User::factory()->create(['login' => $this->login]);
    $creatif->bookSetting()->create(['thumbnail' => 'visuel.jpg']);

    expect($creatif->fresh()->thumbnailUrl())->toContain('/front_desk/visuel.jpg');
});
