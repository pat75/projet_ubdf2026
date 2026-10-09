<?php

use App\Models\User;

/*
 | Book absent ou supprime : 404, meme page que la 404 du portail, et ses
 | liens menent au portail, pas au sous-domaine du book absent.
 */

it('repond 404 avec la page du book introuvable', function () {
    $reponse = $this->get('https://toto-book-quinexistepas.'.config('ubdf.book_domain').'/');

    $reponse->assertNotFound()
        ->assertSee('Book introuvable')
        ->assertSee('Ce book n’existe pas ou n’est plus en ligne.', false);

    expect($reponse->getContent())->not->toContain('toto-book-quinexistepas.');
});

it('garde la 404 du portail sur www, sous-domaine du domaine des books', function () {
    // En production, le portail (www.ultra-book.com) est lui-meme un
    // sous-domaine du domaine des books (ultra-book.com).
    $reponse = $this->get('https://www.'.config('ubdf.book_domain').'/page-qui-nexiste-pas');

    $reponse->assertNotFound()
        ->assertSee('Page introuvable')
        ->assertDontSee('Book introuvable')
        ->assertSee('<meta name="robots" content="noindex', false);
});

it('ne fait pas indexer la 404 d un book', function () {
    $this->get('https://toto-book-quinexistepas.'.config('ubdf.book_domain').'/')
        ->assertNotFound()
        ->assertSee('<meta name="robots" content="noindex', false);
});

it('traite un book supprime comme un book absent', function () {
    $book = User::factory()->create(['login' => 'bookefface']);
    $book->delete();

    $this->get('https://bookefface.'.config('ubdf.book_domain').'/')
        ->assertNotFound()
        ->assertSee('Book introuvable');
});
