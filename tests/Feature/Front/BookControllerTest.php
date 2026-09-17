<?php

use App\Models\User;

beforeEach(function () {
    config(['marques.marques.ub.hotes' => ['ubdf2026.ultra-book.name']]);

    $this->book = User::factory()->create(['login' => 'aurelie-b', 'brand' => 'ub', 'firstname' => 'Aurélie', 'lastname' => 'B.']);
    $this->book->bookSetting()->create(['title' => 'Aurélie B.', 'footer' => 'Contact : aurelie@example.com']);

    $this->galerie = $this->book->galleries()->create([
        'name' => 'Illustrations', 'slug' => 'illustrations', 'status' => 'published', 'position' => 0,
    ]);
    $this->galerie->media()->create([
        'user_id' => $this->book->id,
        'filename' => 'v1.jpg', 'status' => 'published', 'position' => 0, 'title' => 'Affiche',
    ]);

    $this->rubrique = $this->book->sections()->create([
        'title' => 'À propos', 'slug' => 'a-propos', 'is_published' => true, 'is_private' => false, 'position' => 0,
    ]);
    $this->rubrique->articles()->create([
        'user_id' => $this->book->id,
        'title' => 'Présentation', 'slug' => 'presentation', 'body' => '<p>Bonjour</p>', 'status' => 'published', 'position' => 0,
    ]);
});

function hoteBook(string $login, string $chemin = '/'): string
{
    return 'https://'.$login.'.ubdf2026.ultra-book.name'.$chemin;
}

it('affiche l accueil du book avec ses galeries et rubriques', function () {
    $reponse = $this->get(hoteBook('aurelie-b'));

    $reponse->assertOk()
        ->assertSee('Aurélie B.')
        ->assertSee('Illustrations')
        ->assertSee('À propos');
});

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

it('affiche une galerie et ses visuels', function () {
    $this->get(hoteBook('aurelie-b', '/portfolio/illustrations'))
        ->assertOk()
        ->assertSee('Illustrations')
        ->assertSee('Affiche');
});

it('rend 404 pour une galerie inexistante ou non publiee', function () {
    $this->get(hoteBook('aurelie-b', '/portfolio/inconnue'))->assertNotFound();

    $this->galerie->update(['status' => 'draft']);
    $this->get(hoteBook('aurelie-b', '/portfolio/illustrations'))->assertNotFound();
});

it('affiche une rubrique et ses articles', function () {
    $this->get(hoteBook('aurelie-b', '/rubrique/a-propos'))
        ->assertOk()
        ->assertSee('À propos')
        ->assertSee('Présentation')
        ->assertSee('Bonjour', escape: false);
});

it('refuse une rubrique privee a un visiteur', function () {
    $this->rubrique->update(['is_private' => true]);

    $this->get(hoteBook('aurelie-b', '/rubrique/a-propos'))->assertForbidden();
});

it('autorise le proprietaire a voir sa rubrique privee', function () {
    $this->rubrique->update(['is_private' => true]);

    $this->actingAs($this->book)
        ->get(hoteBook('aurelie-b', '/rubrique/a-propos'))
        ->assertOk();
});

it('separe strictement les books entre eux', function () {
    $autre = User::factory()->create(['login' => 'autre-creatif']);
    $autre->bookSetting()->create(['title' => 'Autre']);

    // La galerie d'aurelie-b n'existe pas sous le login d'un autre book.
    $this->get(hoteBook('autre-creatif', '/portfolio/illustrations'))->assertNotFound();
});
