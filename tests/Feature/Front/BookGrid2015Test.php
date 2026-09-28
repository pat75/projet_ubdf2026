<?php

use App\Models\User;

/*
| Modele Grid 2015, passe en Blade/Tailwind/Alpine
| (resources/views/book/grid2015, App\Services\Book\VueGrid2015).
*/

beforeEach(function () {
    $this->book = User::factory()->create(['login' => 'gina-grid', 'firstname' => 'Gina', 'lastname' => 'Grid', 'plan' => 1]);
    $this->book->bookSetting()->create(['theme' => 'mdl_2015_grid', 'diffuse_web' => true, 'title' => 'Tableaux']);

    foreach (['Affiches', 'Carnets'] as $i => $nom) {
        $galerie = $this->book->galleries()->create(['name' => $nom, 'status' => 'published', 'position' => $i]);
        foreach (range(1, 4) as $n) {
            $galerie->media()->create(['user_id' => $this->book->id, 'filename' => "{$nom}{$n}.jpg", 'status' => 'published',
                'title' => "{$nom} {$n}", 'width' => 1200, 'height' => 800]);
        }
    }
});

function urlGrid(string $chemin = '/'): string
{
    return 'https://gina-grid.'.config('ubdf.book_domain').$chemin;
}

it('rend l accueil en tuiles par rubrique, sans jQuery ni Fotorama', function () {
    $html = $this->get(urlGrid())->assertOk()
        ->assertSee('modele-grid', false)
        ->assertDontSee('jquery', false)
        ->assertDontSee('fotorama', false)
        ->getContent();

    expect($html)->toContain('/books/gina-grid/carre_335/Affiches1.jpg')
        ->toContain('/books/gina-grid/carre_335/Carnets1.jpg')
        ->toContain('id="menu-grid"');
});

it('ouvre /portfolio sur la premiere rubrique en diaporama, avec index', function () {
    $html = $this->get(urlGrid('/portfolio'))->assertOk()->getContent();

    expect($html)->toContain('x-data="diaporama(4)"')
        ->toContain('src="/books/gina-grid/source/Affiches1.jpg"')
        ->not->toContain('src="/books/gina-grid/source/Affiches2.jpg"')
        ->toContain('Index des images');
});

it('masque les tuiles du portfolio si le createur l a choisi', function () {
    $conf = json_decode(config('book_themes.mdl_2015_grid.defaut'), true);
    $conf['data']['accueil_ptf_vignette_aff'] = ['accueil_ptf_vignette_aff' => 'false'];
    $this->book->bookSetting->update(['theme_settings' => $conf]);

    $this->get(urlGrid())->assertOk()->assertDontSee('carre_335', false);
});

it('rend la Bio et le contact', function () {
    $this->get(urlGrid('/actualites'))->assertOk()->assertSee('modele-grid', false);
    $this->get(urlGrid('/contact'))->assertOk()->assertSee('name="fm_contact_mail"', false);
});
