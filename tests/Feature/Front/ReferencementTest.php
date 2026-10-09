<?php

use Illuminate\Support\Facades\Cache;

beforeEach(fn () => Cache::flush());

it('donne a l accueil une seule adresse canonique, la racine', function () {
    $racine = rtrim(config('marques.marques.ub.canonique'), '/').'/';

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('<link rel="canonical" href="'.$racine.'">')
        ->and(substr_count($html, '<h1'))->toBe(1);

    // L'ancienne adresse ne sert plus de doublon : 301 vers la racine.
    $this->get('/accueil?ref=ancien')->assertStatus(301)->assertRedirect('/?ref=ancien');
});

it('decrit le site et sa recherche en donnees structurees', function () {
    $this->get('/')->assertOk()
        ->assertSee('"@type":"WebSite"', false)
        ->assertSee('"@type":"SearchAction"', false)
        ->assertSee('"@type":"CollectionPage"', false);
});

it('n indexe pas les pages de resultats de recherche', function () {
    $this->get('/recherche?q=antoine&type_recherche=pseudo')->assertOk()
        ->assertSee('<meta name="robots" content="noindex, follow">', false);
});

it('sert un llms.txt pour les assistants IA', function () {
    $this->get('/llms.txt')->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->assertSee('# Ultra-book', false)
        ->assertSee('sitemap.xml', false);
});
