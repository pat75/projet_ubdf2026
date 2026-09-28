<?php

use App\Models\User;
use App\Services\Book\ContexteBook;
use App\Services\Book\VueZoom2016;
use App\Support\Marque;

/*
| Modele Zoom 2016, passe en Blade/Tailwind/Alpine
| (resources/views/book/zoom2016, App\Services\Book\VueZoom2016).
*/

beforeEach(function () {
    $this->book = User::factory()->create(['login' => 'zoe-zoom', 'firstname' => 'Zoé', 'lastname' => 'Zoom', 'plan' => 1]);
    $this->book->bookSetting()->create(['theme' => 'mdl_2016_zoom', 'diffuse_web' => true, 'description' => 'Illustratrice jeunesse']);

    foreach (['Affiches', 'Carnets'] as $i => $nom) {
        $galerie = $this->book->galleries()->create(['name' => $nom, 'status' => 'published', 'position' => $i]);
        foreach (range(1, 4) as $n) {
            $galerie->media()->create(['user_id' => $this->book->id, 'filename' => "{$nom}{$n}.jpg", 'status' => 'published',
                'title' => "{$nom} {$n}", 'width' => 1200, 'height' => 800]);
        }
    }
});

function urlZoom(string $chemin = '/'): string
{
    return 'https://zoe-zoom.'.config('ubdf.book_domain').$chemin;
}

function vueZoom(User $book, array $conf = []): VueZoom2016
{
    $contexte = new ContexteBook($book, Marque::depuisCode($book->brand));
    $contexte->chargerPortfolio()->pagePortfolio(0);
    $contexte->cont_conf2012 = json_encode(['data' => $conf]);

    return new VueZoom2016($contexte);
}

it('rend le portfolio sans jQuery, Isotope ni Fotorama', function () {
    $this->get(urlZoom())
        ->assertOk()
        ->assertSee('modele-zoom', false)
        ->assertSee('x-data="mosaique"', false)
        ->assertSee('mosaique-zoom', false)
        ->assertDontSee('jquery', false)
        ->assertDontSee('isotop', false)
        ->assertDontSee('fotorama', false);
});

it('sert trois declinaisons au navigateur et reserve la place des visuels', function () {
    $html = $this->get(urlZoom())->getContent();

    expect($html)
        ->toContain('srcset="/books/zoe-zoom/iph_medium/Affiches1.jpg 320w, /books/zoe-zoom/ptf_medium/Affiches1.jpg 550w"')
        ->toContain('data-grand="/books/zoe-zoom/source/Affiches1.jpg"')
        ->toContain('width="1200" height="800"')
        ->toContain('rel="preload" as="image"')
        ->and(substr_count($html, 'loading="lazy"'))->toBe(2);
});

it('propose le filtre par rubrique', function () {
    $this->get(urlZoom())
        ->assertSee('data-rubrique="0__affiches"', false)
        ->assertSee('data-rubrique="1__carnets"', false)
        ->assertSeeInOrder(['Tous', 'Affiches', 'Carnets']);
});

it('borne les visuels a la formule par rubrique, comme le legacy', function () {
    $vue = vueZoom($this->book);
    $vue->b->us_formule_img_nb = 3;

    expect(collect($vue->visuels())->countBy('rubrique')->all())->toBe(['0__affiches' => 3, '1__carnets' => 3]);
});

it('groupe le portfolio par rubrique quand le reglage le demande', function () {
    $conf = json_decode(config('book_themes.mdl_2016_zoom.defaut'), true);
    $conf['data']['ptf_activer_iso_category']['ptf_activer_iso_category'] = 'true';
    $this->book->bookSetting->update(['theme_settings' => $conf]);

    $this->get(urlZoom())->assertSeeInOrder(['<h2', 'Affiches', 'Affiches 1', '<h2', 'Carnets', 'Carnets 1'], false);
});

it('choisit la couleur du texte selon la clarte du bandeau', function () {
    expect(VueZoom2016::sombre('#292929'))->toBeTrue()
        ->and(VueZoom2016::sombre('#dc006b'))->toBeTrue()
        ->and(VueZoom2016::sombre('#ffffff'))->toBeFalse()
        ->and(VueZoom2016::sombre('rgb(240, 230, 140)'))->toBeFalse();

    expect(vueZoom($this->book, ['.ub_couleur_nav' => ['color' => '#ffffff']])->variables())
        ->toContain('--book-bandeau:#ffffff')->toContain('--book-bandeau-texte:#000000');
});

it('refuse une couleur qui sortirait de la declaration CSS', function () {
    expect(VueZoom2016::couleurCss('red;} body{display:none', '#fff'))->toBe('#fff');
});

it('ecarte les noms de fichier employes comme titre', function () {
    expect(VueZoom2016::titreVisuel('fb1cae510efb_rw_1200.png'))->toBe('')
        ->and(VueZoom2016::titreVisuel('Affiche du festival'))->toBe('Affiche du festival');
});

it('decrit le book pour les moteurs : canonique, Open Graph, schema.org', function () {
    $html = $this->get(urlZoom('/accueil'))->getContent();

    expect($html)
        ->toContain('<link rel="canonical" href="https://zoe-zoom.'.config('ubdf.book_domain').'">')
        ->toContain('property="og:image"')
        ->toContain('"@type":"Person"')
        ->toContain('"@type":"ImageGallery"')
        ->toContain('"name":"Zoé Zoom"');
});

it('rend la Bio et le contact', function () {
    $bio = $this->book->sections()->create(['kind' => 'pages', 'title' => 'Parcours', 'is_published' => true, 'position' => 1]);
    $bio->articles()->create(['user_id' => $this->book->id, 'title' => 'Expositions', 'body' => '<p>Diplômée en 2010</p>', 'status' => 'published']);

    $this->get(urlZoom('/actualites'))->assertOk()->assertSee('Diplômée en 2010', false)->assertDontSee('jquery', false);
    $this->get(urlZoom('/contact'))->assertOk()->assertSee('x-data="contactBook"', false)->assertSee('/captcha/contact_book', false);
});

it('masque le formulaire de contact quand le reglage le desactive', function () {
    $conf = json_decode(config('book_themes.mdl_2016_zoom.defaut'), true);
    $conf['data']['ptf_activer_contact']['ptf_activer_contact'] = 'false';
    $this->book->bookSetting->update(['theme_settings' => $conf]);

    $this->get(urlZoom('/contact'))->assertOk()->assertDontSee('x-data="contactBook"', false);
});
