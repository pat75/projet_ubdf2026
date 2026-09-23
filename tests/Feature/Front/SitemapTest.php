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
        ->assertSee('/accueil')
        ->assertSee('/meilleurs-graphistes');
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
