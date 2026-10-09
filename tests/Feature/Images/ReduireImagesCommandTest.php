<?php

use Illuminate\Support\Facades\File;

it('reduit aux dimensions d un depot (1980 x 3600) et sauvegarde la source', function () {
    $stockage = sys_get_temp_dir().'/reduire-'.uniqid();
    app()->useStoragePath($stockage);
    $dossier = $stockage.'/app/public/books/t/e/s/test';
    File::ensureDirectoryExists($dossier);
    imagejpeg(imagecreatetruecolor(3000, 1500), $dossier.'/grand.jpg');
    imagejpeg(imagecreatetruecolor(800, 800), $dossier.'/petit.jpg');
    // Vertical sous 1980 x 3600 : accepte tel quel au depot, donc pas touche.
    imagejpeg(imagecreatetruecolor(1900, 3000), $dossier.'/vertical.jpg');

    $this->artisan('ubdf:images:reduire')->assertSuccessful();

    expect(getimagesize($dossier.'/grand.jpg'))->toMatchArray([0 => 1980, 1 => 990])
        ->and(getimagesize($stockage.'/app/originaux/t/e/s/test/grand.jpg'))->toMatchArray([0 => 3000, 1 => 1500])
        ->and(getimagesize($dossier.'/petit.jpg'))->toMatchArray([0 => 800, 1 => 800])
        ->and(getimagesize($dossier.'/vertical.jpg'))->toMatchArray([0 => 1900, 1 => 3000])
        ->and(file_exists($stockage.'/app/originaux/t/e/s/test/petit.jpg'))->toBeFalse();

    File::deleteDirectory($stockage);
});
