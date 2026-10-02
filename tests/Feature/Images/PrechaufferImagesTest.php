<?php

use App\Services\Images\Declinaison;
use App\Services\Images\GenerateurImages;
use App\Support\DossierBook;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;

beforeEach(function () {
    $this->dossier = DossierBook::chemin('prechauffe-test');
    File::ensureDirectoryExists($this->dossier.'/cms');
    app(ImageManager::class)->createImage(800, 600)->save($this->dossier.'/visuel.jpg');
    app(ImageManager::class)->createImage(800, 600)->save($this->dossier.'/cms/page.jpg');
});

afterEach(function () {
    File::deleteDirectory($this->dossier);
});

it('pre-genere les declinaisons WebP d un book, sans toucher aux images de pages', function () {
    $this->artisan('ubdf:prechauffer-images', ['--login' => ['prechauffe-test'], '--declinaison' => ['front_desk']])
        ->assertSuccessful();

    $generateur = app(GenerateurImages::class);
    $front = Declinaison::nommee('front_desk');

    expect($generateur->cheminCache($this->dossier.'/visuel.jpg', $front, true))->toBeFile()
        ->and($generateur->cheminCache($this->dossier.'/visuel.jpg', $front))->not->toBeFile()
        ->and($generateur->cheminCache($this->dossier.'/cms/page.jpg', $front, true))->not->toBeFile();
});

it('refuse une declinaison inconnue', function () {
    $this->artisan('ubdf:prechauffer-images', ['--declinaison' => ['geante']])->assertFailed();
});

it('se limite aux books d une lettre', function () {
    $this->artisan('ubdf:prechauffer-images', ['--lettre' => ['p'], '--dry-run' => true])
        ->expectsOutputToContain('visuels')
        ->assertSuccessful();

    $this->artisan('ubdf:prechauffer-images', ['--lettre' => ['p'], '--declinaison' => ['front_desk']])->assertSuccessful();

    expect(app(GenerateurImages::class)->cheminCache($this->dossier.'/visuel.jpg', Declinaison::nommee('front_desk'), true))->toBeFile();
});
