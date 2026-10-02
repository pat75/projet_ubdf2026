<?php

use App\Services\Legacy\LegacyFiles;
use App\Support\DossierBook;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->source = sys_get_temp_dir().'/legacy-files-'.uniqid();
    $this->storage = sys_get_temp_dir().'/storage-'.uniqid();
    app()->useStoragePath($this->storage);

    $book = $this->source.'/a/d/adolie';
    File::ensureDirectoryExists($book.'/img_');
    File::ensureDirectoryExists($book.'/img_ptf_small');
    File::ensureDirectoryExists($book.'/img_cms/2019');
    File::put($book.'/img_/visuel.jpg', 'original');
    File::put($book.'/img_ptf_small/visuel.jpg', 'declinaison');
    File::put($book.'/img_cms/2019/page.png', 'cms');
    File::put($book.'/img_cms/2019/shell.php', '<?php');
});

afterEach(function () {
    File::deleteDirectory($this->source);
    File::deleteDirectory($this->storage);
});

it('copie sans toucher a la source par defaut', function () {
    (new LegacyFiles($this->source))->copyLogin('adolie');

    expect(File::get(DossierBook::chemin('adolie', 'visuel.jpg')))->toBe('original')
        ->and(file_exists($this->source.'/a/d/adolie/img_/visuel.jpg'))->toBeTrue();
});

it('deplace les originaux et laisse le reste dans la source', function () {
    $fichiers = new LegacyFiles($this->source, deplacer: true);
    $fichiers->copyLogin('adolie');

    $book = $this->source.'/a/d/adolie';

    expect(File::get(DossierBook::chemin('adolie', 'visuel.jpg')))->toBe('original')
        ->and(File::get(DossierBook::chemin('adolie', 'img_cms/2019/page.png')))->toBe('cms')
        ->and(file_exists($book.'/img_/visuel.jpg'))->toBeFalse()
        ->and(file_exists($book.'/img_cms/2019/page.png'))->toBeFalse()
        // Declinaison et script : jamais repris, restent en place.
        ->and(file_exists($book.'/img_ptf_small/visuel.jpg'))->toBeTrue()
        ->and(file_exists($book.'/img_cms/2019/shell.php'))->toBeTrue()
        ->and(file_exists(DossierBook::chemin('adolie', 'img_cms/2019/shell.php')))->toBeFalse()
        ->and($fichiers->report()['fichiers_deplaces'])->toBe(2);
});

it("n'ecrase pas un fichier deja present et laisse la source", function () {
    File::ensureDirectoryExists(DossierBook::chemin('adolie'));
    File::put(DossierBook::chemin('adolie', 'visuel.jpg'), 'deja la');

    (new LegacyFiles($this->source, deplacer: true))->copyLogin('adolie');

    expect(File::get(DossierBook::chemin('adolie', 'visuel.jpg')))->toBe('deja la')
        ->and(file_exists($this->source.'/a/d/adolie/img_/visuel.jpg'))->toBeTrue();
});

it('en copie, remplace un fichier modifie depuis la passe precedente', function () {
    $fichiers = new LegacyFiles($this->source);
    $fichiers->copyLogin('adolie');

    // Nouveau `recuperer` : le visuel a change sur l'ancien serveur.
    File::put($this->source.'/a/d/adolie/img_/visuel.jpg', 'retouche');
    touch($this->source.'/a/d/adolie/img_/visuel.jpg', time() + 60);

    $relance = new LegacyFiles($this->source);
    $relance->copyLogin('adolie');

    expect(File::get(DossierBook::chemin('adolie', 'visuel.jpg')))->toBe('retouche')
        ->and($relance->fichiers())->toBe(1);
});

it('en copie, ne recopie pas un fichier inchange', function () {
    (new LegacyFiles($this->source))->copyLogin('adolie');

    $relance = new LegacyFiles($this->source);
    $relance->copyLogin('adolie');

    expect($relance->fichiers())->toBe(0);
});
