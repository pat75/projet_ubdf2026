<?php

use App\Models\User;

/*
| Modele Pinter 2013, passe en Blade/Tailwind/Alpine : mise en page de
| Responsive 2014, mosaique filtree depuis la colonne
| (resources/views/book/pinter, App\Services\Book\VuePinter2013).
*/

beforeEach(function () {
    $this->book = User::factory()->create(['login' => 'paul-pint', 'firstname' => 'Paul', 'lastname' => 'Pint', 'plan' => 1]);
    $this->book->bookSetting()->create([
        'theme' => 'mdl_2013_pinter', 'diffuse_web' => true, 'title' => 'Croquis',
        'legacy_payload' => ['us_pf_visuel2012' => 'bandeau.jpg'],
    ]);

    foreach (['Affiches', 'Carnets'] as $i => $nom) {
        $galerie = $this->book->galleries()->create(['name' => $nom, 'status' => 'published', 'position' => $i]);
        foreach (range(1, 3) as $n) {
            $galerie->media()->create(['user_id' => $this->book->id, 'filename' => "{$nom}{$n}.jpg", 'status' => 'published',
                'title' => "{$nom} {$n}", 'width' => 1200, 'height' => 800]);
        }
    }
});

function urlPint(string $chemin = '/'): string
{
    return 'https://paul-pint.'.config('ubdf.book_domain').$chemin;
}

it('rend l accueil en mosaique de tout le portfolio, sans jQuery', function () {
    $html = $this->get(urlPint())->assertOk()
        ->assertSee('mosaique-pinter', false)
        ->assertDontSee('jquery', false)
        ->assertDontSee('isotope', false)
        ->getContent();

    expect(substr_count($html, 'data-visuel '))->toBe(6)
        ->and($html)->toContain('src="/books/paul-pint/source/bandeau.jpg"')
        // Page encadree blanche sur le fond gris du modele.
        ->toContain('background-color: #ffffff');
});

it('filtre la mosaique depuis la colonne', function () {
    $html = $this->get(urlPint('/portfolio'))->assertOk()->getContent();

    expect($html)->toContain("\$dispatch('mosaique-filtrer'")
        ->toContain('Tout afficher');
});

it('masque le bandeau par defaut du modele', function () {
    $this->book->bookSetting->update(['legacy_payload' => ['us_pf_visuel2012' => 'ultra-book_default_980x200.gif']]);

    $this->get(urlPint())->assertDontSee('ultra-book_default_980x200', false);
});

it('rend la Bio et le contact', function () {
    $this->get(urlPint('/actualites'))->assertOk()->assertSee('modele-responsive', false);
    $this->get(urlPint('/contact'))->assertOk()->assertSee('name="fm_contact_mail"', false);
});
