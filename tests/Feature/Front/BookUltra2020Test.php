<?php

use App\Models\User;

/*
| Modeles Ultra-frais / Ultra-zen, passes en Blade/Tailwind/Alpine
| (resources/views/book/ultra2020, App\Services\Book\VueUltra2020).
*/

beforeEach(function () {
    $this->book = User::factory()->create(['login' => 'lea-frais', 'firstname' => 'Léa', 'lastname' => 'Frais']);
    $this->book->bookSetting()->create(['theme' => 'mdl_2020_ultra_frais', 'diffuse_web' => true]);

    foreach (['Affiches', 'Carnets'] as $i => $nom) {
        $galerie = $this->book->galleries()->create(['name' => $nom, 'status' => 'published', 'position' => $i]);
        foreach (range(1, 4) as $n) {
            $galerie->media()->create(['user_id' => $this->book->id, 'filename' => "{$nom}{$n}.jpg", 'status' => 'published',
                'title' => "{$nom} {$n}", 'width' => 1200, 'height' => 800]);
        }
    }
});

function urlUltra(string $login, string $chemin = '/portfolio'): string
{
    return 'https://'.$login.'.'.config('ubdf.book_domain').$chemin;
}

it('rend le portfolio sans jQuery ni Semantic UI', function () {
    $this->get(urlUltra('lea-frais'))
        ->assertOk()
        ->assertSee('theme_ultrafrais', false)
        ->assertSee('x-data="mosaique"', false)
        ->assertDontSee('jquery', false)
        ->assertDontSee('semantic', false);
});

it('charge d emblee le premier ecran et differe les suivants', function () {
    $html = $this->get(urlUltra('lea-frais'))->getContent();

    expect(substr_count($html, 'loading="eager"'))->toBe(6)
        ->and(substr_count($html, 'loading="lazy"'))->toBe(2)
        // Dimensions connues : la place est reservee avant le chargement.
        ->and($html)->toContain('width="1200" height="800"')
        ->and($html)->toContain('srcset="/books/lea-frais/ptf_medium/Affiches1.jpg 550w, /books/lea-frais/source/Affiches1.jpg 1980w"');
});

it('propose le filtre par rubrique', function () {
    $this->get(urlUltra('lea-frais'))
        ->assertSee('data-rubrique="0__affiches"', false)
        ->assertSee('data-rubrique="1__carnets"', false)
        ->assertSeeInOrder(['les projets', 'Tous', 'Affiches', 'Carnets']);
});

it('borne le nombre de visuels a la formule, comme le legacy', function () {
    $contexte = new App\Services\Book\ContexteBook($this->book, App\Support\Marque::depuisCode($this->book->brand));
    $contexte->chargerPortfolio()->pagePortfolio(0);
    $contexte->us_formule_img_nb = 5;

    // Le legacy s'arrete a `>= img_nb` : un visuel de moins que la formule.
    expect((new App\Services\Book\VueUltra2020($contexte))->visuels())->toHaveCount(4);
});

it('dispose Ultra-zen en colonne', function () {
    $this->book->bookSetting->update(['theme' => 'mdl_2020_ultra_zen']);

    $this->get(urlUltra('lea-frais'))
        ->assertOk()
        ->assertSee('theme_ultrazen', false)
        ->assertSee('md:grid-cols-[minmax(0,1fr)_minmax(0,3fr)]', false);
});

it('rend la Bio avec son menu de pages, titres decodes une seule fois', function () {
    $bio = $this->book->sections()->create(['kind' => 'pages', 'title' => 'Parcours', 'is_published' => true, 'position' => 1]);
    $bio->articles()->create(['user_id' => $this->book->id, 'title' => 'Expositions d&#039;originaux', 'body' => '<p>Diplômée en 2010</p>', 'status' => 'published']);
    $bio->articles()->create(['user_id' => $this->book->id, 'title' => 'Ateliers', 'body' => '<p>Ateliers</p>', 'status' => 'published']);

    $this->get(urlUltra('lea-frais', '/actualites'))
        ->assertOk()
        ->assertDontSee('jquery', false)
        ->assertSee('Diplômée en 2010', false)
        ->assertSee('Expositions d&#039;originaux', false)
        ->assertDontSee('&amp;#039;', false);
});

it('rend le contact, envoye a la route du book', function () {
    $this->book->bookSetting->update(['diffuse_web' => true]);

    $this->get(urlUltra('lea-frais', '/contact'))
        ->assertOk()
        ->assertDontSee('jquery', false)
        ->assertSee('x-data="contactBook"', false)
        ->assertSee('name="fm_contact_message"', false)
        ->assertSee('Et si on parlait de votre projet ?');
});
