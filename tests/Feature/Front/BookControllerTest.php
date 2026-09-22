<?php

use App\Models\User;

/*
| Resolution du book sur son sous-domaine. Le rendu des themes est couvert
| par BookThemesTest.
*/

beforeEach(function () {
    $this->book = User::factory()->create(['login' => 'aurelie-b']);
    $this->book->bookSetting()->create(['title' => 'Aurélie B.', 'theme' => 'mdl_2016_zoom']);
    $this->galerie = $this->book->galleries()->create(['name' => 'Illustrations', 'status' => 'published', 'position' => 0]);
});

function hoteBook(string $login, string $chemin = '/'): string
{
    return 'https://'.$login.'.'.config('ubdf.book_domain').$chemin;
}

it('rend 404 pour un login inexistant, avec une page dediee', function () {
    // La 404 generique de Laravel ne dit rien du contexte ; sur un
    // sous-domaine de book, elle est remplacee par une page qui nomme la
    // situation et renvoie vers le portail.
    $this->get(hoteBook('personne-narrive'))
        ->assertNotFound()
        ->assertSee('Ce book n’existe pas ou n’est plus disponible.');
});

it('rend 404 pour un compte supprime', function () {
    $this->book->delete();

    $this->get(hoteBook('aurelie-b'))->assertNotFound();
});

it('ignore le titre qui precede l identifiant, comme le legacy', function () {
    // Un titre de galerie modifie ne doit pas casser les liens indexes.
    $this->get(hoteBook('aurelie-b', '/ancien_titre-p'.$this->galerie->id))->assertOk();
});

it('separe strictement les books entre eux', function () {
    $autre = User::factory()->create(['login' => 'autre-creatif']);
    $autre->bookSetting()->create(['theme' => 'mdl_2016_zoom']);

    // La galerie d'aurelie-b n'apparait pas sous le login d'un autre book.
    $this->get(hoteBook('autre-creatif', '/illustrations-p'.$this->galerie->id))
        ->assertOk()
        ->assertDontSee('Illustrations');
});
