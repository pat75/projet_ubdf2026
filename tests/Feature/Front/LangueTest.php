<?php

use App\Support\Langue;

function site(string $path = '/'): string
{
    return 'https://'.config('ubdf.book_domain').$path;
}

it('normalise la forme POSIX du legacy', function () {
    // Les visiteurs de l'ancien site portent un cookie « lang=fr_FR ».
    expect(Langue::normaliser('fr_FR'))->toBe('fr')
        ->and(Langue::normaliser('en_US'))->toBe('en')
        ->and(Langue::normaliser('ja_JP'))->toBe('ja')
        ->and(Langue::normaliser('en-GB'))->toBe('en')
        ->and(Langue::normaliser('de_DE'))->toBeNull()
        ->and(Langue::normaliser(null))->toBeNull();
});

it('affiche le portail dans la langue de la marque quand rien ne la designe', function () {
    // L'en-tete est explicite : le client de test de Laravel envoie
    // « Accept-Language: en-us,en;q=0.5 » par defaut, ce qui masquerait le
    // repli que ce test verifie.
    $this->withHeader('Accept-Language', 'de-DE,de;q=0.9')
        ->get(site('/'))
        ->assertOk()
        ->assertSee('lang="fr"', false);
});

it('suit le cookie de langue', function () {
    $this->withUnencryptedCookie(Langue::COOKIE, 'en')
        ->get(site('/'))
        ->assertOk()
        ->assertSee('lang="en"', false);
});

it('traduit les chaines du catalogue repris', function () {
    $this->withUnencryptedCookie(Langue::COOKIE, 'en')
        ->get(site('/illustrateur'))
        ->assertOk()
        ->assertSee('Loading...', false);

    $this->withUnencryptedCookie(Langue::COOKIE, 'ja')
        ->get(site('/illustrateur'))
        ->assertOk()
        ->assertSee('読み込んでいます...', false);
});

it('tient compte de l en-tete Accept-Language', function () {
    // Le legacy ne le regardait pas : un visiteur japonais arrivait en
    // francais tant qu'il n'avait pas trouve le selecteur.
    $this->withHeader('Accept-Language', 'ja,en;q=0.8')
        ->get(site('/'))
        ->assertOk()
        ->assertSee('lang="ja"', false);
});

it('retombe sur une langue connue quand celle du navigateur est absente', function () {
    $this->withHeader('Accept-Language', 'de,en;q=0.9')
        ->get(site('/'))
        ->assertOk()
        ->assertSee('lang="en"', false);
});

it('fait primer le cookie sur le navigateur', function () {
    $this->withUnencryptedCookie(Langue::COOKIE, 'fr')
        ->withHeader('Accept-Language', 'ja')
        ->get(site('/'))
        ->assertOk()
        ->assertSee('lang="fr"', false);
});

it('bascule la langue et revient sur la page consultee', function () {
    // Le legacy renvoyait /en sur action.php?lang=en_US, qui affichait
    // l'accueil : on perdait la page en cours.
    $this->withHeader('referer', site('/illustrateur'))
        ->get(site('/en'))
        ->assertRedirect(site('/illustrateur'))
        ->assertPlainCookie(Langue::COOKIE, 'en');
});

it('ignore un referer exterieur au site', function () {
    // Sans ce controle, /en serait une redirection ouverte.
    $this->withHeader('referer', 'https://attaquant.example/piege')
        ->get(site('/en'))
        ->assertRedirect(site('/accueil'));
});

it('rend 404 sur une langue non servie', function () {
    $this->get(site('/de'))->assertNotFound();
});

it('laisse le cookie de langue en clair', function () {
    // Le JavaScript du front 2018 lit `lang`, et les books servis sur les
    // sous-domaines partagent ce cookie.
    $reponse = $this->get(site('/ja'));

    expect($reponse->headers->getCookies()[0]->getValue())->toBe('ja');
});

it('expose la langue au JavaScript repris du front 2018', function () {
    // js_core_pages.js teste `lang == 'fr'` pour choisir le catalogue de
    // mots-cles a charger.
    $this->withUnencryptedCookie(Langue::COOKIE, 'en')
        ->get(site('/'))
        ->assertOk()
        ->assertSee("lang =              'en'", false);
});

it('donne la forme POSIX a og:locale', function () {
    $this->withUnencryptedCookie(Langue::COOKIE, 'ja')
        ->get(site('/'))
        ->assertOk()
        ->assertSee("content='ja_JP'", false);
});
