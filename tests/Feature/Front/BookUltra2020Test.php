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

/*
| Mode edition (EditionBookController)
*/

function entrerEnEdition($test): void
{
    $cible = $test->actingAs($test->book)->get(route('espace.edition-book'))->assertRedirect()->headers->get('Location');
    auth()->logout();
    $test->get($cible)->assertRedirect('/portfolio');
}

it('ouvre le mode edition par un lien signe, a usage unique', function () {
    $cible = $this->actingAs($this->book)->get(route('espace.edition-book'))->headers->get('Location');
    expect($cible)->toStartWith('https://lea-frais.'.config('ubdf.book_domain').'/edition/');

    auth()->logout();
    $this->get($cible)->assertRedirect('/portfolio');
    $this->assertAuthenticatedAs($this->book);

    // Deuxieme usage : refuse.
    auth()->logout();
    $this->get($cible)->assertForbidden();
});

it('refuse un lien falsifie ou destine a un autre book', function () {
    $cible = $this->actingAs($this->book)->get(route('espace.edition-book'))->headers->get('Location');
    auth()->logout();

    $this->get(str_replace('lea-frais.', 'autre-login.', $cible))->assertForbidden();
    $this->get($cible.'x')->assertForbidden();
});

it('affiche le mode edition au seul createur', function () {
    $this->get(urlUltra('lea-frais'))->assertDontSee('Mode édition');

    entrerEnEdition($this);

    $this->get(urlUltra('lea-frais'))
        ->assertSee('Mode édition')
        ->assertSee("x-data=\"texteBook('titre')\"", false)
        ->assertDontSee('ubstats.gif', false);
});

it('enregistre un reglage de la liste blanche, filtre le HTML et le CSS', function () {
    entrerEnEdition($this);
    $url = urlUltra('lea-frais', '/reglages');

    $this->postJson($url, ['cle' => 'theme', 'valeur' => 'theme_black'])->assertOk();
    $this->postJson($url, ['cle' => 'nav_link.name_page', 'valeur' => 'À propos'])->assertOk();
    $this->postJson($url, ['cle' => 'footer', 'valeur' => '<a href="/x" onclick="x()">Lien</a><script>x()</script>'])->assertOk();
    $this->postJson($url, ['cle' => 'expert_css', 'valeur' => 'h1{color:red}</style><script>'])->assertOk();

    $data = $this->book->bookSetting->fresh()->theme_settings['data'];
    expect($data['theme'])->toBe('theme_black')
        ->and($data['nav_link']['name_page'])->toBe('À propos')
        ->and($data['footer'])->not->toContain('onclick')->not->toContain('<script')
        ->and($data['expert_css'])->not->toContain('</style');

    $this->postJson($url, ['cle' => 'theme', 'valeur' => 'rose'])->assertStatus(422);
    $this->postJson($url, ['cle' => 'us_formule', 'valeur' => '3'])->assertStatus(422);
});

it('refuse les reglages d un book a un autre createur', function () {
    $autre = User::factory()->create(['login' => 'autre-creatif']);

    $this->actingAs($autre)->postJson(urlUltra('lea-frais', '/reglages'), ['cle' => 'theme', 'valeur' => 'theme_black'])->assertForbidden();
    $this->postJson(urlUltra('lea-frais', '/reglages'), ['cle' => 'theme', 'valeur' => 'theme_black']);
    expect($this->book->bookSetting->fresh()->theme_settings['data']['theme'] ?? null)->not->toBe('theme_black');
});

describe('portfolio verrouille', function () {
    beforeEach(function () {
        $this->prive = $this->book->galleries()->create(['name' => 'Privé', 'status' => 'published', 'position' => 2, 'password' => 'secret42']);
        $this->prive->media()->create(['user_id' => $this->book->id, 'filename' => 'prive1.jpg', 'status' => 'published', 'title' => 'Prive 1']);
        $this->urlPrive = urlUltra('lea-frais', '/prive-p'.$this->prive->id);
    });

    it('garde le portfolio dans le menu, avec un cadenas et un lien, sans ses visuels', function () {
        $this->get(urlUltra('lea-frais'))
            ->assertOk()
            ->assertSee('data-verrou', false)
            ->assertSee('href="/prive-p'.$this->prive->id.'"', false)
            ->assertSee('rel="nofollow"', false)
            ->assertSee('Protégé par mot de passe')
            ->assertSeeInOrder(['Affiches', 'Carnets', 'Privé'])
            ->assertDontSee('prive1.jpg')
            ->assertDontSee('data-rubrique="2__prive"', false);
    });

    it('affiche le menu meme quand un seul portfolio est ouvert', function () {
        $this->book->galleries()->where('name', 'Carnets')->get()->each->delete();
        $this->book->galleries()->where('name', 'Affiches')->get()->each->delete();
        $autre = $this->book->galleries()->create(['name' => 'Seul', 'status' => 'published', 'position' => 5]);
        $autre->media()->create(['user_id' => $this->book->id, 'filename' => 'seul.jpg', 'status' => 'published']);

        $this->get(urlUltra('lea-frais'))->assertSee('les projets')->assertSee('data-verrou', false);
    });

    it('ne change rien aux autres modeles', function () {
        $this->book->bookSetting->update(['theme' => 'mdl_2016_zoom']);

        $this->get(urlUltra('lea-frais'))->assertOk()->assertDontSee('data-verrou', false);
    });

    it('demande le mot de passe aux couleurs du book', function () {
        $this->get($this->urlPrive)
            ->assertOk()
            ->assertSee('Ce portfolio est protégé')
            ->assertSee('theme_ultrafrais', false)
            ->assertSee('<meta name="robots" content="noindex">', false)
            ->assertSee('href="/"', false)
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertDontSee('prive1.jpg');
    });

    it('refuse un mauvais mot de passe puis annonce le temps restant', function () {
        $this->post($this->urlPrive, ['mot_de_passe' => 'faux'])->assertStatus(422)->assertSee('Mot de passe incorrect.');

        foreach (range(1, 10) as $i) {
            $this->post($this->urlPrive, ['mot_de_passe' => 'faux']);
        }

        $this->post($this->urlPrive, ['mot_de_passe' => 'secret42'])->assertStatus(422)->assertSee('dans 10 minutes');
    });

    it('ouvre le portfolio sur une mosaique deja filtree, sans cadenas ensuite', function () {
        $this->post($this->urlPrive, ['mot_de_passe' => 'secret42'])->assertRedirect($this->urlPrive);

        $this->get($this->urlPrive)
            ->assertOk()
            ->assertSee('prive1.jpg')
            ->assertSee('data-filtre="2__prive"', false)
            ->assertDontSee('data-verrou', false);
    });

    it('reste ouvert a son createur, avec le meme filtre', function () {
        $this->actingAs($this->book)->get($this->urlPrive)
            ->assertOk()->assertSee('prive1.jpg')->assertDontSee('data-verrou', false);
    });

    it('filtre aussi sur un portfolio non protege', function () {
        $affiches = $this->book->galleries()->where('name', 'Affiches')->first();

        $this->get(urlUltra('lea-frais', '/affiches-p'.$affiches->id))
            ->assertOk()->assertSee('data-filtre="0__affiches"', false);
    });
});
