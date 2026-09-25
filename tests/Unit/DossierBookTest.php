<?php

use App\Support\DossierBook;
use Illuminate\Support\Facades\File;

it('segmente le dossier d un book par les trois premiers caracteres du login', function () {
    expect(DossierBook::relatif('adolie'))->toBe('books/a/d/o/adolie')
        ->and(DossierBook::relatif('adolie', 'visuel.jpg'))->toBe('books/a/d/o/adolie/visuel.jpg')
        ->and(DossierBook::relatif('adolie', 'img_cms'))->toBe('books/a/d/o/adolie/img_cms')
        ->and(DossierBook::chemin('adolie', 'v.jpg'))->toBe(storage_path('app/public/books/a/d/o/adolie/v.jpg'));
});

it('met les segments en minuscules sans toucher au login', function () {
    expect(DossierBook::relatif('JeanDupont'))->toBe('books/j/e/a/JeanDupont');
});

it('complete un login court pour garder le book au quatrieme niveau', function () {
    expect(DossierBook::relatif('ab'))->toBe('books/a/b/_/ab')
        ->and(DossierBook::relatif('x'))->toBe('books/x/_/_/x');
});

it('range les dossiers a plat dans leur dossier segmente, sans rien ecraser', function () {
    $stockage = sys_get_temp_dir().'/ubdf-segmenter-'.uniqid();
    app()->useStoragePath($stockage);
    $books = $stockage.'/app/public/books';

    File::ensureDirectoryExists($books.'/adolie/img_cms');
    file_put_contents($books.'/adolie/visuel.jpg', 'x');
    File::ensureDirectoryExists($books.'/pat10');
    // Deja range : ignore. Et un conflit, laisse en place.
    File::ensureDirectoryExists($books.'/b/o/b/bobby');
    File::ensureDirectoryExists($books.'/bobby');

    $this->artisan('ubdf:segmenter-books', ['--dry-run' => true])->assertFailed();
    expect(is_dir($books.'/adolie'))->toBeTrue();

    $this->artisan('ubdf:segmenter-books')->assertFailed();

    expect(is_file($books.'/a/d/o/adolie/visuel.jpg'))->toBeTrue()
        ->and(is_dir($books.'/a/d/o/adolie/img_cms'))->toBeTrue()
        ->and(is_dir($books.'/p/a/t/pat10'))->toBeTrue()
        ->and(is_dir($books.'/adolie'))->toBeFalse()
        ->and(is_dir($books.'/bobby'))->toBeTrue();

    File::deleteDirectory($books.'/bobby');
    $this->artisan('ubdf:segmenter-books')->assertSuccessful();

    File::deleteDirectory($stockage);
});
