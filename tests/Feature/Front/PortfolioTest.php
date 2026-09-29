<?php

use App\Models\User;

beforeEach(function () {
    $this->book = User::factory()->create(['login' => 'aurelie-b', 'brand' => 'ub']);
    $this->book->bookSetting()->create([
        'title' => 'Aurélie B.',
        'diffuse_web' => true,
        'diffuse_ub' => true,
    ]);
    $this->book->media()->create(['status' => 'published', 'filename' => 'v.jpg', 'position' => 0]);
});

it('affiche un book diffuse', function () {
    $this->get($this->book->portfolioUrl())->assertOk();
});

it('redirige vers l URL canonique si le slug est perime', function () {
    $this->get('/portfolio/aurelie-b/mauvais-slug')
        ->assertRedirect($this->book->portfolioUrl());
});

it('rend 404 pour un book non diffuse', function () {
    // in_home_selection est la selection editoriale, pas un critere de
    // publication : un book avec diffuse_web/diffuse_ub a false ne doit
    // pas etre visible, meme s'il est en selection.
    $this->book->bookSetting()->update(['diffuse_web' => false]);

    $this->get($this->book->portfolioUrl())->assertNotFound();
});

it('n affiche pas un book d une autre marque', function () {
    config(['marques.marques.ub.hotes' => ['ubdf2026.ultra-book.name']]);
    $this->book->update(['brand' => 'df']);

    $this->get('https://ubdf2026.ultra-book.name'.parse_url($this->book->portfolioUrl(), PHP_URL_PATH))
        ->assertNotFound();
});

it('redige la description quand celle du book n est qu une liste de mots-cles', function () {
    $this->book->bookSetting()->update(['description' => 'femme, illustration, dessin, art']);

    $html = $this->get($this->book->portfolioUrl())->assertOk()->getContent();

    expect($html)->toContain('découvrez son portfolio et contactez directement ce créatif sur Ultra-book')
        ->and($html)->not->toContain('content="femme, illustration');
});

it('decrit le createur et ses images en donnees structurees', function () {
    $html = $this->get($this->book->portfolioUrl())->assertOk()->getContent();

    expect($html)->toContain('"@type":"ProfilePage"')
        ->and($html)->toContain('"@type":"Person"')
        ->and($html)->toContain('"@type":"BreadcrumbList"')
        ->and($html)->toContain('"copyrightNotice"');
});

it('ferme le microbook aux moteurs et renvoie vers le book', function () {
    $this->get('/microbook_0_0__aurelie-b')->assertOk()
        ->assertSee('<meta name="robots" content="noindex, follow">', false)
        ->assertSee('<link rel="canonical" href="'.$this->book->bookUrl().'">', false);
});
