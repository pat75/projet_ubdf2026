<?php

use App\Models\CmsPage;
use App\Models\CmsPost;

function page_portail(string $path): string
{
    return 'https://'.config('ubdf.book_domain').$path;
}

beforeEach(function () {
    CmsPage::create([
        'legacy_id' => 2,
        'translation_group' => 10,
        'slug' => 'doc',
        'locale' => 'fr',
        'title' => 'Principe de fonctionnement',
        'body' => '<p>Le principe.</p>',
        'published_at' => now()->subYear(),
    ]);

    $this->page = CmsPage::create([
        'legacy_id' => 411,
        'translation_group' => 20,
        'slug' => 'qui-sommes-nous',
        'locale' => 'fr',
        'parent_slug' => 'doc',
        'title' => 'Qui sommes nous',
        'body' => '<p>Ultra-book est une plate-forme.</p>',
        'position' => 1,
        'published_at' => now()->subYear(),
    ]);

    CmsPage::create([
        'legacy_id' => 719,
        'translation_group' => 30,
        'slug' => 'droit-dauteur',
        'locale' => 'fr',
        'parent_slug' => 'doc',
        'title' => 'Droit d’auteur',
        'position' => 2,
        'published_at' => now()->subYear(),
    ]);

    CmsPage::create([
        'legacy_id' => 880,
        'translation_group' => 30,
        'slug' => 'copyright',
        'locale' => 'en',
        'parent_slug' => 'doc',
        'title' => 'Copyright',
        'published_at' => now()->subYear(),
    ]);

    $this->actualite = CmsPost::create([
        'legacy_id' => 500,
        'slug' => 'rencontres-illustrateurs',
        'locale' => 'fr',
        'title' => 'Rencontres illustrateurs',
        'body' => '<p>Une journée à Toulouse.</p>',
        'excerpt' => 'Une journée à Toulouse.',
        'published_at' => now()->subMonth(),
    ]);
});

it('sert une page de documentation', function () {
    $this->get(page_portail('/doc/qui-sommes-nous'))
        ->assertOk()
        ->assertSee('Qui sommes nous')
        ->assertSee('Ultra-book est une plate-forme.', false);
});

it('sert la meme page sous l ancienne forme page__', function () {
    // Le legacy distinguait /doc/x et /page__x ; les deux URL sont indexees.
    $this->get(page_portail('/page__qui-sommes-nous'))
        ->assertOk()
        ->assertSee('Qui sommes nous');
});

it('sert les anciennes URL par marque', function () {
    $this->get(page_portail('/ultra-book__qui-sommes-nous'))->assertOk();
    $this->get(page_portail('/dustfolio__qui-sommes-nous'))->assertOk();
});

it('affiche le sommaire des pages soeurs', function () {
    $this->get(page_portail('/doc/qui-sommes-nous'))
        ->assertOk()
        ->assertSee('Droit d’auteur', false);
});

it('ne melange pas les sommaires des deux langues', function () {
    $this->get(page_portail('/doc/qui-sommes-nous'))
        ->assertOk()
        ->assertDontSee('Copyright');
});

it('sert une page anglaise par son slug', function () {
    // Les deux arbres cohabitent : une page anglaise reste accessible
    // meme quand le portail est en francais.
    $this->get(page_portail('/doc/copyright'))->assertOk()->assertSee('Copyright');
});

it('rend 404 sur un slug inconnu', function () {
    $this->get(page_portail('/doc/nexiste-pas'))->assertNotFound();
});

it('masque une page non publiee', function () {
    CmsPage::create([
        'slug' => 'brouillon',
        'locale' => 'fr',
        'title' => 'Brouillon',
        'published_at' => null,
    ]);

    $this->get(page_portail('/doc/brouillon'))->assertNotFound();
});

it('masque une page dont la date de publication est a venir', function () {
    CmsPage::create([
        'slug' => 'a-venir',
        'locale' => 'fr',
        'title' => 'À venir',
        'published_at' => now()->addWeek(),
    ]);

    $this->get(page_portail('/doc/a-venir'))->assertNotFound();
});

it('liste les actualites', function () {
    $this->withHeader('Accept-Language', 'fr')
        ->get(page_portail('/actus'))
        ->assertOk()
        ->assertSee('Rencontres illustrateurs');
});

it('sert les actualites de la langue par defaut quand la langue courante n en a aucune', function () {
    // Les 74 actualites reprises de WordPress sont toutes en francais :
    // un visiteur anglophone verrait une page vide.
    $this->withHeader('Accept-Language', 'en')
        ->get(page_portail('/actus'))
        ->assertOk()
        ->assertSee('Rencontres illustrateurs');
});

it('sert une actualite', function () {
    $this->get(page_portail('/actus/rencontres-illustrateurs'))
        ->assertOk()
        ->assertSee('Une journée à Toulouse.', false);
});

it('redirige l ancienne adresse encodee d une actualite vers son slug nettoye', function () {
    // WordPress encodait les slugs a apostrophe typographique ; la route les
    // refusait : 5 adresses en 404 dans le sitemap.
    $this->actualite->update(['slug' => 'trois-villes-dillustrateurs']);

    $this->get(page_portail('/actus/trois-villes-d%E2%80%99illustrateurs'))
        ->assertStatus(301)
        ->assertRedirect(page_portail('/actus/trois-villes-dillustrateurs'));

    $this->get(page_portail('/actus/inconnue-d%E2%80%99ici'))->assertNotFound();
});

it('redirige /blog vers les actualites', function () {
    // Le legacy renvoyait vers un site externe (ultra-book.fr/actus).
    $this->get(page_portail('/blog'))
        ->assertRedirect(page_portail('/actus'));
});
