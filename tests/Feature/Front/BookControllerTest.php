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

it('ouvre les videos du book dans leur lecteur', function () {
    $this->book->bookSetting->update(['diffuse_web' => true]);
    $this->galerie->media()->create(['user_id' => $this->book->id, 'filename' => 'clip.jpg', 'status' => 'published',
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);

    $this->get(hoteBook('aurelie-b', '/illustrations-p'.$this->galerie->id))
        ->assertOk()
        ->assertSee('{"clip.jpg":"https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
});

it('n ajoute pas de lecteur a un book sans video', function () {
    $this->book->bookSetting->update(['diffuse_web' => true]);

    $this->get(hoteBook('aurelie-b'))->assertOk()->assertDontSee('youtube-nocookie', false);
});

describe('portfolio protege', function () {
    beforeEach(function () {
        $this->book->bookSetting->update(['diffuse_web' => true]);
        $this->galerie->update(['password' => 'secret42']);
        $this->galerie->media()->create(['user_id' => $this->book->id, 'filename' => 'prive.jpg', 'status' => 'published']);
        $this->url = hoteBook('aurelie-b', '/illustrations-p'.$this->galerie->id);
    });

    it('demande le mot de passe au visiteur et cache les visuels ailleurs', function () {
        $this->get($this->url)->assertOk()->assertSee('Ce portfolio est protégé')->assertDontSee('prive.jpg');
        $this->get(hoteBook('aurelie-b'))->assertOk()->assertDontSee('prive.jpg');
        $this->get('/books/aurelie-b/prive.jpg')->assertHeader('Cache-Control', 'no-store, private');
    });

    it('ouvre le portfolio pour la session avec le bon mot de passe', function () {
        $this->post($this->url, ['mot_de_passe' => 'faux'])->assertStatus(422)->assertSee('Mot de passe incorrect.');
        $this->post($this->url, ['mot_de_passe' => 'secret42'])->assertRedirect($this->url);

        $this->get($this->url)->assertOk()->assertDontSee('Ce portfolio est protégé');
    });

    it('reste ouvert a son createur', function () {
        $this->actingAs($this->book)->get($this->url)->assertOk()->assertDontSee('Ce portfolio est protégé');
    });
});
