<?php

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;

/*
| Titre, description et bloc « a propos » des books (App\Services\Book\VueBook) :
| ce que l'on cherche (nom, metier, ville, specialites) plutot que le
| titre herite du legacy ou les noms de rubriques par defaut.
*/

beforeEach(function () {
    $metier = Category::create(['slug' => 'illustrateur', 'name' => 'Illustrateur', 'is_active' => true]);
    $this->book = User::factory()->create(['login' => 'lea-seo', 'firstname' => 'Léa', 'lastname' => 'Seo',
        'city' => 'lyon', 'category_id' => $metier->id]);
    $this->book->bookSetting()->create(['theme' => 'mdl_2014_responsive', 'diffuse_web' => true, 'title' => 'Léa Seo : Ultra-book']);

    foreach (['Galerie 1', '- Affiches !'] as $i => $nom) {
        $galerie = $this->book->galleries()->create(['name' => $nom, 'status' => 'published', 'position' => $i]);
        $galerie->media()->create(['user_id' => $this->book->id, 'filename' => "v{$i}.jpg", 'status' => 'published']);
    }
});

function pageSeo(): string
{
    return test()->get('https://lea-seo.'.config('ubdf.book_domain').'/')->assertOk()->getContent();
}

it('remplace un titre qui n est que le nom par le nom, le metier et la ville', function () {
    expect(pageSeo())->toContain('<title>Léa Seo — Illustrateur freelance à Lyon | Ultra-book</title>');
});

it('remplace un titre generique herite du legacy par le nom, le metier et la ville', function (string $titre) {
    $this->book->bookSetting->update(['title' => $titre]);

    expect(pageSeo())->toContain('<title>Léa Seo — Illustrateur freelance à Lyon | Ultra-book</title>');
})->with([
    // Constates en production : « de camillegrain | Ultra-book »,
    // « de sandrine-creus Portfolio | Ultra-book », « … Portfolio Portfolio ».
    'Ultra-book de login' => 'Ultra-book de lea-seo',
    'Ultra-book de login Portfolio' => 'Ultra-book de lea-seo Portfolio',
    'Book de login' => 'Book de lea-seo',
    'd’ apostrophe typographique' => 'Ultra-book d’lea-seo',
    'nom suivi de Portfolio' => 'Léa Seo Portfolio',
    'Portfolio seul' => 'Portfolio',
]);

it('garde un titre saisi qui dit quelque chose', function () {
    $this->book->bookSetting->update(['title' => 'Atelier de gravure']);

    expect(pageSeo())->toContain('<title>Atelier de gravure | Ultra-book</title>');
});

it('decrit le book sans les rubriques par defaut ni leur ponctuation', function () {
    // « Galerie 1 » reste dans le menu du book ; seule la description l'ignore.
    expect(pageSeo())->toContain('<meta name="description" content="Léa Seo, illustrateur freelance à Lyon. Portfolio : Affiches.">');
});

it('montre les specialites IA en liens vers les pages mot-cle', function () {
    $tag = Tag::create(['label' => 'aquarelle', 'lang' => 'fr']);
    $tag->media()->attach($this->book->media()->first()->id);

    expect(pageSeo())
        ->toContain('aria-label="À propos"')
        ->toContain('>aquarelle</a>')
        ->toContain('/images/aquarelle');
});
