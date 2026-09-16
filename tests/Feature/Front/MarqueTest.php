<?php

use App\Models\CmsPage;
use App\Support\Marque;

beforeEach(function () {
    config([
        'marques.marques.ub.hotes' => ['ubdf2026.ultra-book.name', 'ultra-book.com'],
        'marques.marques.df.hotes' => ['ubdf-dust-2026.ultra-book.name', 'dustfolio.com'],
    ]);
});

function hote(string $host, string $path = '/'): string
{
    return 'https://'.$host.$path;
}

it('reconnait un hote par egalite, pas par sous-chaine', function () {
    // Le legacy comparait avec preg_match('/'.$cle.'/i', $hote) : la cle
    // servait de motif, et « ultra-book » reconnaissait n'importe quel hote
    // qui contenait ces lettres.
    expect(Marque::depuisHote('ultra-book.com')->code)->toBe('ub')
        ->and(Marque::depuisHote('dustfolio.com')->code)->toBe('df')
        ->and(Marque::depuisHote('faux-ultra-book.com.attaquant.net')->code)->toBe('ub');

    // Celui-la n'est reconnu par aucune marque : il retombe sur la marque
    // par defaut, et non sur « ub » parce qu'il contient « ultra-book ».
    expect(Marque::depuisHote('faux-ultra-book.com.attaquant.net')->estDefaut())->toBeTrue();
});

it('ignore le prefixe www et le port', function () {
    expect(Marque::depuisHote('www.dustfolio.com')->code)->toBe('df')
        ->and(Marque::depuisHote('dustfolio.com:8443')->code)->toBe('df');
});

it('retombe sur la marque par defaut pour un hote inconnu', function () {
    expect(Marque::depuisHote('inconnu.example')->code)->toBe('ub')
        ->and(Marque::depuisHote(null)->code)->toBe('ub');
});

it('sert le portail Ultra-book sur le domaine de developpement', function () {
    // Le pied de page porte un lien vers l'autre marque : c'est la balise
    // og:site_name qui dit sous quelle marque la page est servie.
    $this->get(hote('ubdf2026.ultra-book.name'))
        ->assertOk()
        ->assertSee("content='Ultra-book'", false)
        ->assertDontSee("content='Dustfolio'", false);
});

it('sert le portail Dustfolio sur son propre hote', function () {
    $this->get(hote('ubdf-dust-2026.ultra-book.name'))
        ->assertOk()
        ->assertSee("content='Dustfolio'", false)
        ->assertDontSee("content='Ultra-book'", false);
});

it('ne prend pas un sous-domaine reserve pour un book', function () {
    // Rien n'empechait, dans le legacy, qu'un compte prenne le login
    // « www » et capte le sous-domaine correspondant.
    $this->get(hote('www.ubdf2026.ultra-book.name'))->assertOk()->assertDontSee('BOOK · login');
    $this->get(hote('api.ubdf2026.ultra-book.name'))->assertOk()->assertDontSee('BOOK · login');
});

it('sert bien un book sur un sous-domaine ordinaire', function () {
    // Le motif d'exclusion ne doit pas mordre au-dela du mot reserve :
    // « dfx » reste un login valide.
    $this->get(hote('dfx.ubdf2026.ultra-book.name'))
        ->assertOk()
        ->assertSee('BOOK · login = dfx');
});

it('substitue le nom de la marque dans les contenus editoriaux', function () {
    CmsPage::create([
        'slug' => 'qui-sommes-nous',
        'locale' => 'fr',
        'title' => 'Qui sommes nous',
        'body' => '<p>Ultra-book est une plate-forme, éditée par POLYGUN.</p>',
        'published_at' => now()->subYear(),
    ]);

    // Dustfolio n'a jamais eu de pages a lui : le legacy servait celles
    // d'Ultra-book en y remplacant le nom juste avant l'affichage.
    $this->get(hote('ubdf-dust-2026.ultra-book.name', '/doc/qui-sommes-nous'))
        ->assertOk()
        ->assertSee('Dustfolio est une plate-forme', false)
        ->assertSee('DustWare SAS', false);

    $this->get(hote('ubdf2026.ultra-book.name', '/doc/qui-sommes-nous'))
        ->assertOk()
        ->assertSee('Ultra-book est une plate-forme', false)
        ->assertSee('POLYGUN', false);
});

it('donne a chaque marque son adresse de contact et son logo', function () {
    expect(Marque::depuisCode('ub')->email)->toBe('contact@ultra-book.net')
        ->and(Marque::depuisCode('df')->email)->toBe('contact@dustfolio.com')
        ->and(Marque::depuisCode('df')->logo)->toBe('/img_front_df/dustfolio.svg');
});

it('connait le domaine canonique de production de chaque marque', function () {
    // Les URL absolues (courriels, sitemap, og:url) doivent porter le
    // domaine public, pas celui du poste de developpement.
    expect(Marque::depuisCode('ub')->canonique)->toBe('https://www.ultra-book.com')
        ->and(Marque::depuisCode('df')->canonique)->toBe('https://www.dustfolio.com');
});

it('reconnait les domaines de production avec et sans www', function () {
    foreach (['www.ultra-book.com', 'ultra-book.com'] as $hote) {
        expect(Marque::depuisHote($hote)->code)->toBe('ub');
    }

    foreach (['www.dustfolio.com', 'dustfolio.com'] as $hote) {
        expect(Marque::depuisHote($hote)->code)->toBe('df');
    }
});

it('repartit les ressources par dossier, comme le legacy', function () {
    // image_dir du legacy : img_front et img_front_df, pas un suffixe de
    // nom de fichier.
    expect(Marque::depuisCode('ub')->asset('logo.svg'))->toBe('/img_front/logo.svg')
        ->and(Marque::depuisCode('df')->asset('logo.svg'))->toBe('/img_front_df/logo.svg');
});
