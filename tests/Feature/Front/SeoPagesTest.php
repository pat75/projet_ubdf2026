<?php

use App\Models\CmsPage;
use App\Support\Metier;

/*
 * Referencement des pages du portail : titres et descriptions propres a
 * chaque page, sans double echappement.
 */

it('donne a chaque page metier un titre et une description qui lui sont propres', function () {
    $html = $this->get('/illustrateur')->assertOk()->getContent();

    expect($html)->toContain('<title>Illustrateurs freelance : portfolios et books | Ultra-book</title>')
        ->and($html)->toContain('name="description" content="Les portfolios des illustrateurs freelance');
});

it('garde les descriptions sous 160 caracteres', function () {
    foreach (Metier::blocsAccueil() as $metier) {
        expect(mb_strlen(Metier::descriptionSeo($metier['slug'], 'Ultra-book')))->toBeLessThanOrEqual(160);
    }

    expect(mb_strlen(config('marques.marques.ub.description')))->toBeLessThanOrEqual(160);
});

it('n echappe pas deux fois le titre d une page', function () {
    CmsPage::query()->create(['slug' => 'essai', 'locale' => 'fr', 'title' => "Conditions d'utilisation", 'body' => '<p>Texte & suite</p>', 'published_at' => now()]);

    $html = $this->get('/doc/essai')->assertOk()->getContent();

    expect($html)->toContain('<title>Conditions d&#039;utilisation | Ultra-book</title>')
        ->and($html)->not->toContain('&amp;#039;')
        ->and($html)->toContain('name="description" content="Texte &amp; suite"');
});

it('decode un titre stocke deja encode par le legacy', function () {
    expect(texte_seo('Illustration &amp; Infographie'))->toBe('Illustration & Infographie')
        ->and(texte_seo('<p>un  texte   long à couper</p>', 12))->toBe('un texte…');
});

it('partage une image 1200 x 630 propre a la marque', function () {
    $html = $this->get('/illustrateur')->assertOk()->getContent();

    expect($html)->toContain('/img_front/partage/ultra-book.png')
        ->and($html)->toContain("og:image:width' \t\tcontent='1200'")
        ->and($html)->toContain('content="summary_large_image"')
        ->and(file_exists(public_path('img_front/partage/ultra-book.png')))->toBeTrue()
        ->and(file_exists(public_path('img_front/partage/dustfolio.png')))->toBeTrue();
});

it('relie les versions de langue d une page Dustfolio, y compris a adresse traduite', function () {
    config(['marques.marques.df.hotes' => ['ubdf-dust-2026.ultra-book.name']]);

    $html = $this->get('https://ubdf-dust-2026.ultra-book.name/fr/creer-un-book')->assertOk()->getContent();

    expect($html)->toContain('hreflang="en" href="'.rtrim(config('marques.marques.df.canonique'), '/').'/en/create-a-book"')
        ->and($html)->toContain('hreflang="fr" href="'.rtrim(config('marques.marques.df.canonique'), '/').'/fr/creer-un-book"')
        ->and($html)->toContain('hreflang="x-default"');
});

it('ne met pas de hreflang sur Ultra-book, monolingue', function () {
    expect($this->get('/illustrateur')->getContent())->not->toContain('hreflang=');
});
