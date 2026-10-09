<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(fn () => Cache::flush());

function bookDiffuse(array $attrs = [], bool $diffuse = true): User
{
    $creatif = User::factory()->create($attrs + ['brand' => 'ub']);
    $creatif->bookSetting()->create([
        'theme' => 'mdl_2016_zoom', 'diffuse_web' => $diffuse, 'diffuse_ub' => $diffuse,
    ]);
    $creatif->media()->create(['filename' => 'v.jpg', 'status' => 'published']);

    return $creatif;
}

it('sert un index de sitemap', function () {
    bookDiffuse();

    $this->get('/sitemap.xml')->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('sitemap-pages.xml')
        ->assertSee('sitemap-books-1.xml');
});

it('liste les pages du portail', function () {
    $this->get('/sitemap-pages.xml')->assertOk()
        ->assertSee('<loc>'.rtrim(config('marques.marques.ub.canonique'), '/').'/</loc>', false)
        ->assertDontSee('/accueil</loc>', false)
        ->assertSee('/meilleurs-graphistes');
});

it('ne liste que les pages statiques de la langue servie, sans doublon', function () {
    App\Models\CmsPage::query()->create(['slug' => 'doc', 'locale' => 'fr', 'title' => 'Doc', 'published_at' => now()]);
    App\Models\CmsPage::query()->create(['slug' => 'doc', 'locale' => 'en', 'title' => 'Doc', 'published_at' => now()]);
    App\Models\CmsPage::query()->create(['slug' => 'legal-notice', 'locale' => 'en', 'title' => 'Legal notice', 'published_at' => now()]);

    $xml = $this->get('/sitemap-pages.xml')->assertOk()->getContent();

    expect(substr_count($xml, '/doc/doc</loc>'))->toBe(1)
        ->and($xml)->not->toContain('/doc/legal-notice');
});

it('ne liste que les books diffuses', function () {
    $visible = bookDiffuse(['login' => 'ariane']);
    bookDiffuse(['login' => 'cache'], diffuse: false);
    // Diffuse mais vide : le portail ne le montre pas, le sitemap non plus.
    bookDiffuse(['login' => 'vide'])->media()->delete();
    bookDiffuse(['login' => 'suspendu', 'blocked_at' => now()]);

    $reponse = $this->get('/sitemap-books-1.xml')->assertOk();

    expect($reponse->getContent())
        ->toContain($visible->bookUrl())
        ->toContain('<lastmod>')
        ->not->toContain('cache.')
        ->not->toContain('vide.')
        ->not->toContain('suspendu.');
});

it('sert un robots.txt qui ferme l espace et le back-office', function () {
    $this->get('/robots.txt')->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('Disallow: /espace')
        ->assertSee('Disallow: /admin')
        ->assertSee('Sitemap: ');
});

it('ecrit public/robots.txt avec les sitemaps des deux marques', function () {
    $chemin = public_path('robots.txt');
    $avant = file_exists($chemin) ? file_get_contents($chemin) : null;

    try {
        @unlink($chemin);
        $this->artisan('ubdf:robots')->assertSuccessful();

        expect(file_get_contents($chemin))
            ->toContain('Disallow: /espace')
            ->toContain('Sitemap: '.rtrim(config('marques.marques.ub.canonique'), '/').'/sitemap.xml')
            ->toContain('Sitemap: '.rtrim(config('marques.marques.df.canonique'), '/').'/sitemap.xml');
    } finally {
        $avant === null ? @unlink($chemin) : file_put_contents($chemin, $avant);
    }
});

it('liste les pages image indexables avec leur visuel', function () {
    $creatif = bookDiffuse(['login' => 'imagier']);
    $creatif->bookSetting->update(['allow_ai_analysis' => true]);
    $riche = $creatif->media()->create(['filename' => 'riche.jpg', 'status' => 'published', 'ai_title' => 'Renard roux',
        'ai_description' => str_repeat('Un renard roux dans la neige. ', 5), 'analysed_at' => now()]);
    $mince = $creatif->media()->create(['filename' => 'mince.jpg', 'status' => 'published', 'ai_title' => 'Chat',
        'ai_description' => 'Un chat.', 'analysed_at' => now()]);
    foreach (['renard', 'neige', 'hiver', 'animal', 'roux'] as $mot) {
        $tag = App\Models\Tag::create(['label' => $mot, 'lang' => 'fr']);
        $tag->media()->attach([$riche->id, $mince->id]);
    }

    $this->get('/sitemap.xml')->assertSee('sitemap-images-1.xml');

    expect($this->get('/sitemap-images-1.xml')->assertOk()->getContent())
        ->toContain('/image/'.$riche->id.'/renard-roux</loc>')
        ->toContain('<image:loc>'.$riche->url().'</image:loc>')
        ->not->toContain('mince.jpg');
});

it('n indexe qu une page image par titre et par createur, page et sitemap d accord', function () {
    $creatif = bookDiffuse(['login' => 'doublon']);
    $creatif->bookSetting->update(['allow_ai_analysis' => true]);
    $riche = fn (string $fichier) => $creatif->media()->create(['filename' => $fichier, 'status' => 'published',
        'ai_title' => 'Illustration graphique', 'ai_description' => str_repeat('Un renard roux dans la neige. ', 5), 'analysed_at' => now()]);
    [$premiere, $seconde] = [$riche('a.jpg'), $riche('b.jpg')];
    foreach (['renard', 'neige', 'hiver', 'animal', 'roux'] as $mot) {
        App\Models\Tag::firstOrCreate(['label' => $mot, 'lang' => 'fr'])->media()->attach([$premiere->id, $seconde->id]);
    }

    expect($this->get('/sitemap-images-1.xml')->assertOk()->getContent())
        ->toContain('/image/'.$premiere->id.'/')
        ->not->toContain('/image/'.$seconde->id.'/');

    $this->get('/image/'.$premiere->id.'/illustration-graphique')->assertOk()->assertDontSee('noindex', false);
    $this->get('/image/'.$seconde->id.'/illustration-graphique')->assertOk()->assertSee('noindex, follow', false);
});

it('ecarte les books en sommeil du sitemap et des moteurs', function () {
    // Dernier visuel il y a plus de 5 ans et moins de 5 visuels : abandonne.
    $dormeur = bookDiffuse(['login' => 'dormeur']);
    $dormeur->media()->update(['created_at' => now()->subYears(6)]);
    // Ancien mais fourni : garde.
    $fourni = bookDiffuse(['login' => 'fourni']);
    foreach (range(1, 4) as $i) {
        $fourni->media()->create(['filename' => "f{$i}.jpg", 'status' => 'published']);
    }
    $fourni->media()->update(['created_at' => now()->subYears(6)]);
    $actif = bookDiffuse(['login' => 'actif']);

    expect($this->get('/sitemap-books-1.xml')->assertOk()->getContent())
        ->toContain($actif->bookUrl())
        ->toContain($fourni->bookUrl())
        ->not->toContain($dormeur->bookUrl());

    $this->get($dormeur->bookUrl().'/')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, follow');
    $this->get($actif->bookUrl().'/')->assertOk()->assertHeaderMissing('X-Robots-Tag');
});

it('declare les visuels des books pour Google Images', function () {
    $creatif = bookDiffuse(['login' => 'galeriste']);

    expect($this->get('/sitemap-books-1.xml')->assertOk()->getContent())
        ->toContain('xmlns:image=')
        ->toContain('<image:loc>'.$creatif->media()->first()->url().'</image:loc>');
});

it('ne met plus les vieilles actualites dans le sitemap', function () {
    App\Models\CmsPost::create(['slug' => 'mozy-backup', 'locale' => 'fr', 'title' => 'Mozy', 'published_at' => now()->subYears(15)]);

    $this->get('/sitemap-pages.xml')->assertOk()->assertDontSee('/actus/mozy-backup');
});
