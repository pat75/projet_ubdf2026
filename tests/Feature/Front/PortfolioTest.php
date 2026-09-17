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
