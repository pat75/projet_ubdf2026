<?php

use App\Models\User;
use App\Services\Book\ContexteBook;
use App\Services\Book\VueResponsive2014;
use App\Support\Marque;

/*
| Modele Responsive 2014, passe en Blade/Tailwind/Alpine
| (resources/views/book/responsive, App\Services\Book\VueResponsive2014).
*/

beforeEach(function () {
    $this->book = User::factory()->create(['login' => 'remi-resp', 'firstname' => 'Rémi', 'lastname' => 'Resp', 'plan' => 1]);
    $this->book->bookSetting()->create(['theme' => 'mdl_2014_responsive', 'diffuse_web' => true, 'title' => 'Peintures']);

    foreach (['Affiches', 'Carnets'] as $i => $nom) {
        $galerie = $this->book->galleries()->create(['name' => $nom, 'status' => 'published', 'position' => $i]);
        foreach (range(1, 8) as $n) {
            $galerie->media()->create(['user_id' => $this->book->id, 'filename' => "{$nom}{$n}.jpg", 'status' => 'published',
                'title' => "{$nom} {$n}", 'width' => 1200, 'height' => 800]);
        }
    }
    $this->galerie = $this->book->galleries()->first();
});

function urlResp(string $chemin = '/'): string
{
    return 'https://remi-resp.'.config('ubdf.book_domain').$chemin;
}

function reglerResp(User $book, array $data): void
{
    $conf = json_decode(config('book_themes.mdl_2014_responsive.defaut'), true);
    $book->bookSetting->update(['theme_settings' => ['data' => array_merge($conf['data'], $data)]]);
}

it('rend l accueil en tuiles par rubrique, sans jQuery ni Fotorama', function () {
    $html = $this->get(urlResp())->assertOk()
        ->assertSee('modele-responsive', false)
        ->assertDontSee('jquery', false)
        ->assertDontSee('fotorama', false)
        ->getContent();

    expect($html)->toContain('/books/remi-resp/carre_368/Affiches1.jpg')
        ->toContain('/books/remi-resp/carre_183/Affiches2.jpg')
        ->toContain('href="/affiches-p'.$this->galerie->id.'"');
});

it('rend le portfolio en diaporama, premiere image seule chargee d emblee', function () {
    $html = $this->get(urlResp('/affiches-p'.$this->galerie->id))->assertOk()->getContent();

    expect($html)->toContain('x-data="diaporama(8)"')
        ->toContain('src="/books/remi-resp/source/Affiches1.jpg"')
        ->toContain('rel="preload" as="image"')
        // Les suivantes ne sont demandees qu'a l'approche.
        ->not->toContain('src="/books/remi-resp/source/Affiches2.jpg"')
        ->and(substr_count($html, 'aria-label="Image '))->toBe(8);
});

it('applique les reglages de typographie en feuille de style filtree', function () {
    reglerResp($this->book, [
        '.ub_font_menut' => ['fontFamily' => 'Oswald', 'color' => '#e04545', 'fontSize' => '18px'],
        '.ub_font_menu_newsr' => ['color' => 'red;} body{display:none'],
    ]);

    $html = $this->get(urlResp())->getContent();

    expect($html)->toContain('.ub_font_menut{font-family:Oswald;color:#e04545;font-size:18px}')
        ->toContain('family=Oswald')
        ->not->toContain('display:none');
});

it('montre le pictogramme maison, un intitule, ou masque l entree', function () {
    reglerResp($this->book, ['ub_menu_titre_ptf' => ['form_text' => 'Projets'], 'ub_menu_titre_accueil' => ['form_text' => '']]);

    $this->get(urlResp())->assertSee('Projets')->assertDontSee('<span class="sr-only">Accueil</span>', false);
});

it('presente les visuels a la suite en mode image, avec legendes', function () {
    reglerResp($this->book, ['ptf_type_presentation' => ['ptf_type_presentation' => 'image'], 'ptf_titre_aff' => ['ptf_titre_aff' => 'true']]);

    $this->get(urlResp('/affiches-p'.$this->galerie->id))
        ->assertDontSee('x-data="diaporama', false)
        ->assertSeeInOrder(['Affiches 1', 'Affiches 2']);
});

it('borne la formule gratuite par rubrique', function () {
    $contexte = new ContexteBook($this->book, Marque::depuisCode($this->book->brand));
    $contexte->chargerPortfolio()->pagePortfolio($this->galerie->id);
    $contexte->us_formule_img_nb = 3;

    expect((new VueResponsive2014($contexte))->diapositives())->toHaveCount(3);
});

it('rend la Bio et un contact qui n est plus une rubrique de pages', function () {
    $bio = $this->book->sections()->create(['kind' => 'pages', 'title' => 'Parcours', 'is_published' => true, 'position' => 1]);
    $bio->articles()->create(['user_id' => $this->book->id, 'title' => 'Expositions', 'body' => '<p>Diplômé en 2010</p>', 'status' => 'published']);

    $this->get(urlResp('/actualites'))->assertOk()->assertSee('Diplômé en 2010', false);
    $this->get(urlResp('/contact'))->assertOk()
        ->assertSee('x-data="contactBook"', false)
        ->assertSee('<link rel="canonical" href="https://remi-resp.'.config('ubdf.book_domain').'/contact">', false);
});
