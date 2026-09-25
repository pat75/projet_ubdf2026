<?php

use App\Support\Langue;
use App\Support\Marque;

beforeEach(function () {
    config([
        'marques.marques.ub.hotes' => ['ubdf2026.ultra-book.name'],
        'marques.marques.df.hotes' => ['ubdf-dust-2026.ultra-book.name'],
    ]);
});

function ub(string $path = '/'): string
{
    return 'https://ubdf2026.ultra-book.name'.$path;
}

function df(string $path = '/'): string
{
    return 'https://ubdf-dust-2026.ultra-book.name'.$path;
}

it('normalise la forme POSIX du legacy', function () {
    // Les visiteurs de l'ancien site portent un cookie « lang=fr_FR ».
    expect(Langue::normaliser('fr_FR'))->toBe('fr')
        ->and(Langue::normaliser('en_US'))->toBe('en')
        ->and(Langue::normaliser('en-GB'))->toBe('en')
        ->and(Langue::normaliser('de_DE'))->toBeNull()
        ->and(Langue::normaliser(null))->toBeNull();

    // Le japonais est mis de cote : le catalogue existe, la langue n'est
    // pas ouverte.
    expect(Langue::normaliser('ja_JP'))->toBeNull();
});

it('declare Ultra-book monolingue et Dustfolio multilingue', function () {
    expect(Marque::depuisCode('ub')->multilingue())->toBeFalse()
        ->and(Marque::depuisCode('ub')->locale())->toBe('fr')
        ->and(Marque::depuisCode('df')->multilingue())->toBeTrue()
        ->and(Marque::depuisCode('df')->locale())->toBe('en');
});

it('sert Ultra-book en francais, sans segment de langue', function () {
    // Le cookie et le navigateur ne doivent rien y changer : le site est
    // francais, et une seule adresse existe par page.
    $this->withUnencryptedCookie(Langue::COOKIE, 'en')
        ->withHeader('Accept-Language', 'en-US,en;q=0.9')
        ->get(ub('/'))
        ->assertOk()
        ->assertSee('lang="fr"', false);
});

it('refuse une URL prefixee sur Ultra-book', function () {
    // Publier /fr/illustrateur a cote de /illustrateur donnerait deux
    // adresses pour la meme page francaise.
    $this->get(ub('/fr'))->assertNotFound();
    $this->get(ub('/en'))->assertNotFound();
    $this->get(ub('/en/illustrateur'))->assertNotFound();
});

it('renvoie Dustfolio vers l anglais par defaut', function () {
    $this->withHeader('Accept-Language', 'de-DE')
        ->get(df('/'))
        ->assertRedirect(df('/en'));
});

it('conserve le chemin et la requete en ajoutant le segment', function () {
    $this->withHeader('Accept-Language', 'de-DE')
        ->get(df('/recherche?q=illustration'))
        ->assertRedirect(df('/en/recherche?q=illustration'));
});

it('sert Dustfolio dans la langue du segment', function () {
    $this->get(df('/en'))->assertOk()->assertSee('lang="en"', false);
    $this->get(df('/fr'))->assertOk()->assertSee('lang="fr"', false);
});

it('fait primer l URL sur le cookie', function () {
    // Principe repris de Tesli : sur une URL qui porte sa langue, c'est
    // l'URL qui fait foi — sinon un moteur indexerait la page anglaise
    // avec un contenu francais.
    $this->withUnencryptedCookie(Langue::COOKIE, 'fr')
        ->get(df('/en'))
        ->assertOk()
        ->assertSee('lang="en"', false);
});

it('suit le cookie pour choisir vers quelle langue rediriger', function () {
    $this->withUnencryptedCookie(Langue::COOKIE, 'fr')
        ->get(df('/'))
        ->assertRedirect(df('/fr'));
});

it('comprend le cookie de l ancien site', function () {
    $this->withUnencryptedCookie(Langue::COOKIE_LEGACY, 'fr_FR')
        ->get(df('/'))
        ->assertRedirect(df('/fr'));
});

it('tient compte de l en-tete Accept-Language', function () {
    // Le legacy ne le regardait pas.
    $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9')
        ->get(df('/'))
        ->assertRedirect(df('/fr'));
});

it('ignore une langue que la marque ne sert pas', function () {
    $this->withUnencryptedCookie(Langue::COOKIE, 'ja')
        ->withHeader('Accept-Language', 'ja-JP')
        ->get(df('/'))
        ->assertRedirect(df('/en'));
});

it('traduit les chaines du catalogue repris', function () {
    $this->get(df('/en/illustrateur'))->assertOk()->assertSee('Loading...', false);
    $this->get(df('/fr/illustrateur'))->assertOk()->assertSee('Chargement...', false);
});

it('donne a Dustfolio ses propres accroches, pas celles d Ultra-book traduites', function () {
    // Relevees sur https://www.dustfolio.com/en.
    $this->get(df('/en'))
        ->assertOk()
        ->assertSee('Find the best creative portfolios.', false)
        ->assertSee('Dustfolio, create an online portfolio', false);

    $this->get(ub('/'))
        ->assertOk()
        ->assertSee('Trouvez les meilleurs portfolios de créatifs.', false)
        ->assertSee('Portfolios freelance, illustrateur', false);
});

it('expose la langue au JavaScript du portail', function () {
    // resources/js/portail/recherche.js lit <html lang> pour choisir le
    // catalogue de mots-cles (fr ou en).
    $this->get(df('/en'))->assertOk()->assertSee('<html class="no-js" lang="en">', false);
});

it('donne la forme POSIX a og:locale', function () {
    $this->get(df('/en'))->assertOk()->assertSee("content='en_US'", false);
    $this->get(ub('/'))->assertOk()->assertSee("content='fr_FR'", false);
});

it('ne prefixe pas les points d entree techniques', function () {
    // Le JavaScript du front 2018 les appelle a des chemins ecrits en dur :
    // les prefixer les rendrait introuvables sur Dustfolio.
    foreach (['/captcha_img', '/cache_js/data_stats.json'] as $chemin) {
        $this->get(df($chemin))->assertOk();
        $this->get(ub($chemin))->assertOk();
    }
});
