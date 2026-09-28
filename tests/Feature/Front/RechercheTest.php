<?php

use App\Models\BookSetting;
use App\Models\Category;
use App\Models\Media;
use App\Models\User;

beforeEach(function () {
    $this->categorie = Category::create(['slug' => 'illustrateur', 'name' => 'Illustrateur']);
    $this->autre = Category::create(['slug' => 'photographe', 'name' => 'Photographe']);

    $this->aquarelliste = book('nolwenn', $this->categorie, 'aquarelle,illustration jeunesse,presse');
    $this->graveur = book('hectorm', $this->categorie, 'gravure,estampe,illustration jeunesse');
    $this->photographe = book('lisep', $this->autre, 'portrait,aquarelle');
});

function book(string $login, Category $categorie, string $keywords, bool $selection = false): User
{
    $user = User::create([
        'login' => $login,
        'email' => $login.'@example.test',
        'password' => 'secret',
        'category_id' => $categorie->id,
        'firstname' => ucfirst($login),
        'lastname' => 'Créatif',
        'in_home_selection' => true,
        'in_directory' => true,
        'is_selected' => $selection,
    ]);

    Media::create([
        'user_id' => $user->id,
        'filename' => $login.'.jpg',
        'title' => 'Visuel',
        'status' => 'published',
    ]);

    BookSetting::create([
        'user_id' => $user->id,
        'keywords' => $keywords,
        'diffuse_web' => true,
        'diffuse_ub' => true,
    ]);

    return $user;
}

function url_portail(string $path): string
{
    return 'https://'.config('ubdf.book_domain').$path;
}

it('trouve les books par mot-cle', function () {
    $this->get(url_portail('/recherche?q=aquarelle'))
        ->assertOk()
        ->assertSee('nolwenn')
        ->assertSee('lisep')
        ->assertDontSee('hectorm');
});

it('classe en tete le book qui porte le plus de termes recherches', function () {
    // hectorm porte « gravure » et « illustration jeunesse », nolwenn un seul.
    $reponse = $this->get(url_portail('/recherche?q=gravure,illustration jeunesse'));

    $reponse->assertOk();

    $position = fn (string $login) => strpos($reponse->getContent(), $login);

    expect($position('hectorm'))->toBeLessThan($position('nolwenn'));
});

it('limite la recherche a une categorie', function () {
    $this->get(url_portail('/recherche?q=aquarelle&anu_type=illustrateur'))
        ->assertOk()
        ->assertSee('nolwenn')
        ->assertDontSee('lisep');
});

it('cherche sur le nom en mode pseudo', function () {
    $this->get(url_portail('/recherche?q=hectorm&recherche=pseudo'))
        ->assertOk()
        ->assertSee('hectorm')
        ->assertDontSee('nolwenn');
});

it('refuse une requete de moins de trois caracteres', function () {
    // Regle du legacy : en deca, la requete ne discrimine rien.
    $this->get(url_portail('/recherche?q=aq'))
        ->assertOk()
        ->assertSee('au moins trois caractères', false)
        ->assertDontSee('nolwenn');
});

it('annonce l absence de resultat', function () {
    $this->get(url_portail('/recherche?q=tapisserie'))
        ->assertOk()
        ->assertSee('Aucun portfolio', false);
});

it('filtre sur la selection editoriale', function () {
    book('selectionne', $this->categorie, 'aquarelle', selection: true);

    $this->get(url_portail('/recherche?q=aquarelle&flt_sel=true'))
        ->assertOk()
        ->assertSee('selectionne')
        ->assertDontSee('nolwenn');
});

it('sert le contrat JSON du front 2018 sur /rechercher_submit', function () {
    $reponse = $this->getJson(url_portail('/rechercher_submit?q=aquarelle&recherche=mcles&anu_type=tous&suite=0'));

    $reponse->assertOk();

    // Ces cles sont lues telles quelles par js2019/js_core_cards.js.
    $reponse->assertJsonStructure([
        '*' => ['us_id', 'us_key', 'us_dir', 'us_prenom', 'us_nom', 'us_type',
            'us_pf_img_vignette', 'us_path', 'img', 'slider'],
    ]);

    expect(collect($reponse->json())->pluck('us_dir')->all())
        ->toContain('nolwenn');
});

it('rend un tableau vide plutot qu une erreur sur une requete trop courte', function () {
    // Un code 4xx laisserait le JavaScript sur son indicateur de chargement.
    $this->getJson(url_portail('/rechercher_submit?q=aq'))
        ->assertOk()
        ->assertExactJson([]);
});

it('sert les cartes suivantes du defilement', function () {
    $reponse = $this->getJson(url_portail('/recherche/cartes/1?q=aquarelle'));

    $reponse->assertOk()->assertJsonStructure(['html', 'count', 'fin']);

    // Trois books au total : la deuxieme page est vide et close le defilement.
    expect($reponse->json('count'))->toBe(0)
        ->and($reponse->json('fin'))->toBeTrue();
});

it('ne redirige plus /recherche vers l accueil', function () {
    $this->get(url_portail('/recherche'))->assertOk()->assertSee('Rechercher un portfolio');
});

it('neutralise les jokers de LIKE', function () {
    // Sans echappement, « % » ramenerait tous les books.
    $this->get(url_portail('/recherche?q=%25%25%25'))
        ->assertOk()
        ->assertDontSee('nolwenn');
});

it('sert la page /search avec le bloc de recherche en mode ajax', function () {
    $this->get(url_portail('/search'))
        ->assertOk()
        ->assertSee('Trouvez les meilleurs portfolios de créatifs.', false)
        ->assertSee('data-ajax', false)
        ->assertSee('id="resultats_recherche"', false);
});

it('rend les resultats de /search en fragment HTML pour l ajax', function () {
    $reponse = $this->getJson(url_portail('/search?q=aquarelle'), ['X-Requested-With' => 'XMLHttpRequest']);

    $reponse->assertOk()->assertJsonStructure(['html', 'total', 'ubdf' => ['cartes_url', 'cartes_params']]);

    expect($reponse->json('html'))->toContain('nolwenn')->not->toContain('hectorm')
        ->and($reponse->json('html'))->not->toContain('Trouvez les meilleurs portfolios')
        ->and($reponse->json('total'))->toBe(2);
});

it('journalise les recherches par mots-cles, pas par nom', function () {
    $this->get(url_portail('/search?q=Aquarelle'));
    $this->get(url_portail('/search?q=hectorm&recherche=pseudo'));

    expect(\App\Models\SearchQuery::pluck('q')->all())->toBe(['aquarelle']);
});

it('affiche les mots-cles les plus recherches sur 90 jours sur /search sans requete', function () {
    foreach (['gravure', 'gravure', 'aquarelle'] as $q) {
        \App\Models\SearchQuery::create(['q' => $q, 'brand' => 'ub']);
    }
    $ancienne = \App\Models\SearchQuery::create(['q' => 'tapisserie', 'brand' => 'ub']);
    $ancienne->forceFill(['created_at' => now()->subDays(91)])->save();

    $this->get(url_portail('/search'))
        ->assertOk()
        ->assertSeeInOrder(['Les recherches du moment', 'gravure', 'aquarelle'])
        ->assertDontSee('tapisserie');
});

it('suggere les mots-cles des books des trois caracteres', function () {
    \App\Support\SuggestionsMotsCles::oublier();

    // « illustration jeunesse » est porte par deux books : il passe en tete.
    $this->getJson(url_portail('/recherche/suggestions?q=Illu'))
        ->assertOk()
        ->assertExactJson([['mot' => 'illustration jeunesse', 'total' => 2]]);

    // Accents et milieu de mot-cle : « jeu » trouve « illustration jeunesse ».
    expect(collect($this->getJson(url_portail('/recherche/suggestions?q=jeu'))->json())->pluck('mot')->all())
        ->toBe(['illustration jeunesse']);

    $this->getJson(url_portail('/recherche/suggestions?q=aq'))->assertOk()->assertExactJson([]);
});

it('affiche les portfolios du moment tires des recherches du moment', function () {
    foreach (['gravure', 'gravure', 'aquarelle'] as $q) {
        \App\Models\SearchQuery::create(['q' => $q, 'brand' => 'ub']);
    }

    $reponse = $this->get(url_portail('/search'))->assertOk()->assertSee('Les portfolios du moment');

    // « gravure » d'abord (hectorm), puis « aquarelle » : un autre book.
    $html = explode('Les portfolios du moment', $reponse->getContent())[1];
    expect($html)->toContain('user_hectorm')
        ->and(substr_count($html, 'class="ui card '))->toBeLessThanOrEqual(5);
});

it('pointe la loupe du menu vers /search', function () {
    $this->get(url_portail('/accueil'))
        ->assertOk()
        ->assertSee('href="'.lien('search').'"', false);
});
