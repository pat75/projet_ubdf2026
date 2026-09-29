<?php

/*
 * Contenus SEO / GEO des pages metier et des pages d'accroche
 * (config/seo_contenus.php).
 */

it('affiche l introduction et les questions frequentes d une page metier, en FAQPage', function () {
    $html = $this->get('/illustrateur')->assertOk()->getContent();

    expect($html)->toContain('class="metier_intro"')
        ->and($html)->toContain('<summary>Comment contacter un illustrateur ?</summary>')
        ->and($html)->toContain('"@type":"FAQPage"')
        ->and($html)->toContain('"@type":"BreadcrumbList"');
});

it('donne a chaque page d accroche son propre titre et son H1, sans FAQ', function () {
    $html = $this->get('/comment-trouver-un-illustrateur')->assertOk()->getContent();

    expect($html)->toContain('<title>Comment trouver un illustrateur ? Méthode et portfolios | Ultra-book</title>')
        ->and($html)->toContain('>Comment trouver un illustrateur ?</h1>')
        ->and($html)->not->toContain('"@type":"FAQPage"');
});

it('ne laisse aucune page d accroche sans contenu propre', function () {
    $sansContenu = array_diff(
        array_keys(config('seo_routes.landings')),
        array_keys(config('seo_contenus.landings')),
    );

    expect($sansContenu)->toBe([]);

    foreach (config('seo_contenus.landings') as $page) {
        expect(mb_strlen($page['description']))->toBeLessThanOrEqual(160);
    }
});

it('reprend les questions frequentes dans llms.txt', function () {
    $this->get('/llms.txt')->assertOk()
        ->assertSee('## Questions fréquentes', false)
        ->assertSee('## Pages thématiques', false);
});

describe('Dustfolio en anglais', function () {
    beforeEach(function () {
        config(['marques.marques.df.hotes' => ['ubdf-dust-2026.ultra-book.name']]);
    });

    it('sert les pages metier sous une adresse anglaise, en anglais', function () {
        $html = $this->get('https://ubdf-dust-2026.ultra-book.name/en/illustrator')->assertOk()->getContent();

        expect($html)->toContain('<title>Freelance Illustrators: portfolios | Dustfolio</title>')
            ->and($html)->toContain('How do I contact illustrators?')
            // L'hote de developpement contient « ultra-book » : on vise la marque, pas l'adresse.
            ->and($html)->not->toContain('alt="Ultra-book"')
            ->and($html)->not->toContain('on Ultra-book')
            ->and($html)->not->toContain('@ultra-book.net');
    });

    it('redirige l ancienne adresse francaise de la version anglaise', function () {
        $this->get('https://ubdf-dust-2026.ultra-book.name/en/illustrateur')
            ->assertStatus(301)
            ->assertRedirect('https://ubdf-dust-2026.ultra-book.name/en/illustrator');
    });

    it('garde les adresses francaises et les pages d accroche en francais seulement', function () {
        $this->get('https://ubdf-dust-2026.ultra-book.name/fr/illustrateur')->assertOk();
        $this->get('https://ubdf-dust-2026.ultra-book.name/fr/graphistes-freelance')->assertOk();
        $this->get('https://ubdf-dust-2026.ultra-book.name/en/graphistes-freelance')->assertNotFound();
    });
});
