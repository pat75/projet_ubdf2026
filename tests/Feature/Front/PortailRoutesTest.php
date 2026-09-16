<?php

use App\Models\Category;
use App\Models\Media;
use App\Models\User;

beforeEach(function () {
    $this->domain = config('ubdf.book_domain');

    $category = Category::create(['slug' => 'illustrateur', 'name' => 'Illustrateur']);

    $this->book = User::create([
        'login' => 'pat10',
        'email' => 'pat10@example.test',
        'password' => 'secret',
        'category_id' => $category->id,
        'firstname' => 'Amélie',
        'lastname' => 'Falière',
        'is_published' => true,
        'in_directory' => true,
    ]);

    Media::create([
        'user_id' => $this->book->id,
        'filename' => 'visuel.jpg',
        'title' => 'Un visuel',
        'status' => 'published',
    ]);
});

function portail(string $path): string
{
    return 'https://'.config('ubdf.book_domain').$path;
}

it('affiche l accueil avec les cartes de books', function () {
    $this->get(portail('/accueil'))
        ->assertOk()
        ->assertSee('ui card', false)
        ->assertSee('Amélie Falière', false);
});

it('sert les categories metier', function () {
    $this->get(portail('/illustrateur'))->assertOk();
});

it('redirige les anciennes URL vers l URL canonique', function (string $ancienne, string $cible) {
    $this->get(portail($ancienne))->assertRedirect(portail($cible));
})->with([
    ['/portfolios', '/accueil'],
    ['/recherche', '/accueil'],
    ['/graphisme', '/graphiste'],
    ['/illustration', '/illustrateur'],
]);

it('respecte le contrat JSON du defilement infini', function () {
    $response = $this->get(portail('/accueil__0__sel__all'))->assertOk();

    // Cles attendues par js2019/js_core_cards.js : renommer casse le front.
    $response->assertJsonStructure([['us_id', 'us_key', 'us_dir', 'us_type',
        'us_prenom', 'us_nom', 'us_pf_img_vignette', 'us_path', 'img', 'slider']]);
});

it('refuse une selection inconnue dans l URL de defilement', function () {
    $this->get(portail('/accueil__0__nimporte__all'))->assertNotFound();
});

it('redirige un slug de portfolio errone vers l URL canonique', function () {
    $this->get(portail('/portfolio/pat10/mauvais-slug'))
        ->assertRedirect($this->book->portfolioUrl());
});

it('rend une image par defaut quand le visuel a disparu du disque', function () {
    // La base legacy reference des fichiers absents : la carte ne doit pas
    // afficher d'icone cassee.
    $this->get(portail('/books/pat10/fichier-absent.jpg'))->assertOk();
});

it('refuse une remontee d arborescence sur les visuels', function () {
    $this->get(portail('/books/pat10/..%2F..%2F.env'))->assertNotFound();
});

it('affiche l annuaire alphabetique', function () {
    $this->get(portail('/annuaire'))->assertOk()->assertSee('Amélie Falière', false);
    $this->get(portail('/annuaire_p'))->assertOk();
});

it('remplit les attributs data des cartes', function () {
    // Une methode publique sur le composant masquerait la variable de meme
    // nom dans la vue : les data-* sortiraient vides et le clic sur une
    // carte n'ouvrirait aucun book.
    $html = $this->get(portail('/illustrateur'))->assertOk()->getContent();

    preg_match("/data-slider='([^']*)'/", $html, $slider);
    preg_match("/data-user_detail='([^']*)'/", $html, $detail);

    $slider = json_decode(html_entity_decode($slider[1] ?? '{}'), true);
    $detail = json_decode(html_entity_decode($detail[1] ?? '{}'), true);

    expect($slider['book_img'] ?? [])->not->toBeEmpty()
        ->and($detail['book_prenom_nom'] ?? '')->toBe('Amélie Falière');
});

it('affiche un bloc par metier sur l accueil', function () {
    $this->get(portail('/accueil'))
        ->assertOk()
        ->assertSee('metier_group coultxt_illustrateur', false)
        ->assertSee('Dernière sélection illustrateur freelance', false)
        ->assertSee('voir_tous_metier_link', false)
        ->assertSee('cat_link_txt', false);
});

it('sert les cartes suivantes du defilement, masquees pour le fondu', function () {
    $reponse = $this->get(portail('/cartes/illustrateur/1'))->assertOk();

    $reponse->assertJsonStructure(['html', 'count', 'fin']);
    expect($reponse->json('fin'))->toBeTrue();
});

it('reserve les blocs d accroche a l accueil', function () {
    // Video, « Creer votre portfolio », « Une selection de qualite » et
    // « Installer mon site internet pro » sont des accroches d'accueil :
    // elles feraient doublon sur une page de metier ou l'annuaire.
    $this->get(portail('/accueil'))
        ->assertSee('video_header', false)
        ->assertSee('bloc_accueil_entreprise2020', false)
        ->assertSee('bloc_accueil_ubsitepro', false);

    foreach (['/illustrateur', '/annuaire'] as $page) {
        $this->get(portail($page))
            ->assertDontSee('video_header', false)
            ->assertDontSee('bloc_accueil_entreprise2020', false)
            ->assertDontSee('bloc_accueil_ubsitepro', false)
            ->assertDontSee('bloc_last_recherche', false);
    }
});

it('sert au defilement des cartes activables par le JavaScript du front', function () {
    // Le clic qui ouvre un book en pleine page est pose par btn_slide() sur
    // « #user_<login> », a partir de data-slider. Une carte chargee au
    // defilement doit donc porter le meme identifiant et le meme diaporama
    // qu'une carte rendue au chargement, sans quoi elle reste muette.
    $html = $this->get(portail('/cartes/illustrateur/0'))->assertOk()->json('html');

    expect($html)->toContain('id="user_pat10"')
        ->toContain('data-user="pat10"')
        ->toContain('newitem_hide');

    preg_match("/data-slider='([^']*)'/", $html, $slider);
    $slider = json_decode(html_entity_decode($slider[1] ?? '{}'), true);

    expect($slider['book_img'] ?? [])->not->toBeEmpty();
});

it('sert les compteurs globaux au format attendu par le front', function () {
    // js_core_pages.js appelle ce chemin exact au chargement de chaque page
    // et alimente les compteurs du menu avec ces cles.
    $reponse = $this->get(portail('/cache_js/data_stats.json'))->assertOk();

    $reponse->assertJsonStructure([
        'menu_stats' => [
            'nb_book', 'nb_selection', 'nb_visuel', 'nb_galerie',
            'nb_book_illustrateur', 'nb_book_illustrateur_jeunesse', 'nb_book_graphiste',
        ],
        'menu_book_exemple',
    ]);

    // Les nombres sont formates a la francaise, le front les affiche tels quels.
    expect($reponse->json('menu_stats.nb_book'))->toBeString();
});
