<?php

use App\Models\User;
use App\Support\DossierBook;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->creatif = User::factory()->create(['login' => 'testeur']);
    $this->actingAs($this->creatif);
});

afterEach(function () {
    File::deleteDirectory(DossierBook::chemin('testeur'));
});

it('depose une image envoyee depuis l editeur Redactor et rend son URL', function () {
    $reponse = $this->post(route('espace.pages.upload-image'), [
        'file' => UploadedFile::fake()->image('photo.jpg', 800, 600),
    ]);

    $reponse->assertOk()->assertJsonStructure(['filelink']);

    $url = $reponse->json('filelink');
    expect($url)->toContain('/books/testeur/cms/');

    $chemin = DossierBook::chemin('testeur', 'img_cms/'.basename(parse_url($url, PHP_URL_PATH)));
    expect(File::exists($chemin))->toBeTrue();
});

it('refuse un fichier qui n est pas une image', function () {
    $this->post(route('espace.pages.upload-image'), [
        'file' => UploadedFile::fake()->create('script.php', 10, 'text/plain'),
    ])->assertSessionHasErrors('file');
});

it('liste les images deposees par le createur, la plus recente d abord', function () {
    $this->post(route('espace.pages.upload-image'), ['file' => UploadedFile::fake()->image('ancienne.jpg')]);
    $this->travel(1)->minutes();
    $this->post(route('espace.pages.upload-image'), ['file' => UploadedFile::fake()->image('recente.jpg')]);

    $reponse = $this->getJson(route('espace.pages.images.index'))->assertOk();

    expect($reponse->json())->toHaveCount(2)
        ->and($reponse->json('0.nom'))->toBe('recente')
        ->and($reponse->json())->each->toHaveKeys(['id', 'url', 'nom', 'taille', 'creeeLe']);
});

it('remplace le fichier d une image, sans changer son URL', function () {
    $depot = $this->post(route('espace.pages.upload-image'), ['file' => UploadedFile::fake()->image('photo.jpg')]);
    $image = $this->creatif->pageImages()->sole();
    $ancienChemin = DossierBook::chemin('testeur', 'img_cms/'.$image->filename);

    $reponse = $this->post(route('espace.pages.images.update', $image), ['file' => UploadedFile::fake()->image('nouvelle.jpg')])
        ->assertOk();

    expect($reponse->json('url'))->toBe($depot->json('filelink'))
        ->and(File::exists($ancienChemin))->toBeTrue();
});

it('supprime une image, du disque et de la base', function () {
    $this->post(route('espace.pages.upload-image'), ['file' => UploadedFile::fake()->image('photo.jpg')]);
    $image = $this->creatif->pageImages()->sole();
    $chemin = DossierBook::chemin('testeur', 'img_cms/'.$image->filename);

    $this->deleteJson(route('espace.pages.images.destroy', $image))->assertOk();

    expect(File::exists($chemin))->toBeFalse()
        ->and(App\Models\PageImage::find($image->id))->toBeNull();
});

it('interdit de toucher aux images d un autre createur', function () {
    $autre = User::factory()->create(['login' => 'autre']);
    $this->post(route('espace.pages.upload-image'), ['file' => UploadedFile::fake()->image('photo.jpg')]);
    $image = $this->creatif->pageImages()->sole();
    $this->actingAs($autre);

    $this->post(route('espace.pages.images.update', $image), ['file' => UploadedFile::fake()->image('x.jpg')])->assertForbidden();
    $this->deleteJson(route('espace.pages.images.destroy', $image))->assertForbidden();

    File::deleteDirectory(DossierBook::chemin('autre'));
});
