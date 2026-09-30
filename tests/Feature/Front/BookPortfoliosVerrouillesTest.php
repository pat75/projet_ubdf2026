<?php

use App\Models\User;

/*
| Portfolio protege dans les menus des books : Grid 2015, Classique 2015,
| Zoom 2016 et Responsive 2014 (Ultra 2020 : BookUltra2020Test).
*/

beforeEach(function () {
    $this->book = User::factory()->create(['login' => 'lea-verrou']);
    $this->book->bookSetting()->create(['theme' => 'mdl_2014_responsive', 'diffuse_web' => true]);

    foreach (['Affiches', 'Carnets'] as $i => $nom) {
        $galerie = $this->book->galleries()->create(['name' => $nom, 'status' => 'published', 'position' => $i]);
        $galerie->media()->create(['user_id' => $this->book->id, 'filename' => "{$nom}.jpg", 'status' => 'published', 'width' => 800, 'height' => 600]);
    }

    $this->prive = $this->book->galleries()->create(['name' => 'Privé', 'status' => 'published', 'position' => 2, 'password' => 'secret42']);
    $this->prive->media()->create(['user_id' => $this->book->id, 'filename' => 'prive1.jpg', 'status' => 'published']);
});

function urlVerrou(string $chemin = '/'): string
{
    return 'https://lea-verrou.'.config('ubdf.book_domain').$chemin;
}

dataset('themes', [
    'Responsive 2014' => ['mdl_2014_responsive', '/affiches-p', 'Affiches'],
    'Classique 2015' => ['mdl_2015_classique', '/affiches-p', 'Affiches'],
    'Grid 2015' => ['mdl_2015_grid', '/affiches-p', 'Affiches'],
    'Zoom 2016' => ['mdl_2016_zoom', '/portfolio', null],
]);

it('montre le portfolio verrouille dans le menu, avec un cadenas', function (string $theme, string $page, ?string $ouvert) {
    $this->book->bookSetting->update(['theme' => $theme]);
    $chemin = $ouvert ? $page.$this->book->galleries()->where('name', $ouvert)->value('id') : $page;

    $this->get(urlVerrou($chemin))
        ->assertOk()
        ->assertSee('data-verrou', false)
        ->assertSee('href="/prive-p'.$this->prive->id.'"', false)
        ->assertSee('Protégé par mot de passe')
        ->assertDontSee('prive1.jpg');
})->with('themes');

it('demande le mot de passe dans le gabarit du book', function (string $theme) {
    $this->book->bookSetting->update(['theme' => $theme]);

    $this->get(urlVerrou('/prive-p'.$this->prive->id))
        ->assertOk()
        ->assertSee('Ce portfolio est protégé')
        ->assertSee('bg-book-fond', false)
        ->assertSee('<meta name="robots" content="noindex">', false)
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertDontSee('prive1.jpg');
})->with('themes');

it('ouvre le portfolio et retire le cadenas apres le bon mot de passe', function (string $theme) {
    $this->book->bookSetting->update(['theme' => $theme]);
    $url = urlVerrou('/prive-p'.$this->prive->id);

    $this->post($url, ['mot_de_passe' => 'secret42'])->assertRedirect($url);

    $this->get($url)->assertOk()->assertSee('prive1.jpg')->assertDontSee('data-verrou', false);
})->with('themes');

it('ouvre Zoom sur la mosaique filtree', function () {
    $this->book->bookSetting->update(['theme' => 'mdl_2016_zoom']);
    $carnets = $this->book->galleries()->where('name', 'Carnets')->value('id');

    $this->get(urlVerrou('/carnets-p'.$carnets))->assertOk()->assertSee('data-filtre="1__carnets"', false);
});
