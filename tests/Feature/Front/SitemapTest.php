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

    $reponse = $this->get('/sitemap-books-1.xml')->assertOk();

    expect($reponse->getContent())
        ->toContain($visible->bookUrl())
        ->not->toContain('cache.');
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
