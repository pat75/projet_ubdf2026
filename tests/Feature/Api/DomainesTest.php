<?php

use App\Models\Domaine;
use App\Models\User;

beforeEach(function () {
    config(['ubdf.proxy_secret' => 'secret-du-vps']);

    $this->creatif = User::factory()->create(['login' => 'jeanne', 'brand' => 'ub', 'firstname' => 'Jeanne', 'lastname' => 'Dupont']);
    $this->creatif->bookSetting()->create(['theme' => 'mdl_2016_zoom', 'diffuse_web' => true, 'diffuse_ub' => true]);
    $this->creatif->media()->create(['filename' => 'v.jpg', 'status' => 'published']);
});

function domaine(User $u, string $statut = 'actif'): Domaine
{
    return Domaine::create(['user_id' => $u->id, 'nom' => 'jeanne-dupont.fr', 'statut' => $statut]);
}

it('autorise Caddy a demander un certificat pour un domaine servi', function (string $statut, int $code) {
    domaine($this->creatif, $statut);

    $this->get('/api/v1/domaines/autorise?domain=JEANNE-DUPONT.fr')->assertStatus($code);
})->with([
    'actif' => ['actif', 200],
    'dns pointe' => ['dns_ok', 200],
    'en attente' => ['en_attente', 404],
    'suspendu' => ['suspendu', 404],
]);

it('refuse un domaine inconnu', function () {
    $this->get('/api/v1/domaines/autorise?domain=inconnu.fr')->assertNotFound();
    $this->get('/api/v1/domaines/autorise')->assertNotFound();
});

it('sert le book sur le domaine relaye par le VPS', function () {
    domaine($this->creatif);

    $this->get('https://passerelle.exemple/', ['X-Forwarded-Host' => 'jeanne-dupont.fr', 'X-Proxy-Secret' => 'secret-du-vps'])
        ->assertOk()
        ->assertSee('Jeanne Dupont');
});

it('ignore X-Forwarded-Host sans le secret du VPS', function () {
    domaine($this->creatif);

    $this->get('https://passerelle.exemple/', ['X-Forwarded-Host' => 'jeanne-dupont.fr', 'X-Proxy-Secret' => 'faux'])
        ->assertDontSee('Jeanne Dupont');
});

it('repond 404 pour un domaine relaye qui n est plus servi', function () {
    domaine($this->creatif, 'suspendu');

    $this->get('https://passerelle.exemple/', ['X-Forwarded-Host' => 'jeanne-dupont.fr', 'X-Proxy-Secret' => 'secret-du-vps'])
        ->assertNotFound();
});

it('fait pointer les liens du book vers le domaine perso, mais pas les images', function () {
    domaine($this->creatif);

    $page = $this->get('https://passerelle.exemple/', ['X-Forwarded-Host' => 'jeanne-dupont.fr', 'X-Proxy-Secret' => 'secret-du-vps'])
        ->assertOk()->getContent();

    $sousDomaine = 'jeanne.'.App\Support\Marque::depuisCode('ub')->domaineBooks;

    expect($page)->toContain('<link rel="canonical" href="https://jeanne-dupont.fr')
        ->and($page)->not->toMatch('#https://'.preg_quote($sousDomaine, '#').'/(?!books/)#');
});

it('redirige le sous-domaine du book vers son domaine perso actif', function () {
    domaine($this->creatif);

    $this->get($this->creatif->bookUrl().'/?x=1')->assertRedirect('https://jeanne-dupont.fr/?x=1')->assertStatus(301);
});

it('laisse le sous-domaine servir les images et les domaines pas encore actifs', function () {
    domaine($this->creatif, 'dns_ok');
    $this->get($this->creatif->bookUrl().'/')->assertOk();

    Domaine::query()->update(['statut' => 'actif']);
    $this->get($this->creatif->bookUrl().'/books/jeanne/v.jpg')->assertStatus(200);
});
