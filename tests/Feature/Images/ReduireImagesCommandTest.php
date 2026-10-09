<?php

use Illuminate\Support\Facades\File;

it('reduit les originaux trop grands et sauvegarde la source', function () {
    $stockage = sys_get_temp_dir().'/reduire-'.uniqid();
    app()->useStoragePath($stockage);
    $dossier = $stockage.'/app/public/books/t/e/s/test';
    File::ensureDirectoryExists($dossier);
    imagejpeg(imagecreatetruecolor(3000, 1500), $dossier.'/grand.jpg');
    imagejpeg(imagecreatetruecolor(800, 800), $dossier.'/petit.jpg');

    $this->artisan('ubdf:images:reduire')->assertSuccessful();

    expect(getimagesize($dossier.'/grand.jpg'))->toMatchArray([0 => 2000, 1 => 1000])
        ->and(getimagesize($stockage.'/app/originaux/t/e/s/test/grand.jpg'))->toMatchArray([0 => 3000, 1 => 1500])
        ->and(getimagesize($dossier.'/petit.jpg'))->toMatchArray([0 => 800, 1 => 800])
        ->and(file_exists($stockage.'/app/originaux/t/e/s/test/petit.jpg'))->toBeFalse();

    File::deleteDirectory($stockage);
});
