<?php

use App\Models\User;

/*
| Modele Classique 2015, passe en Blade/Tailwind/Alpine : mise en page de
| Responsive 2014 (resources/views/book/responsive) plus un bandeau de
| visuels et un grand visuel d'accueil (App\Services\Book\VueClassique2015).
*/

beforeEach(function () {
    $this->book = User::factory()->create(['login' => 'clara-clas', 'firstname' => 'Clara', 'lastname' => 'Clas', 'plan' => 1]);
    $this->book->bookSetting()->create([
        'theme' => 'mdl_2015_classique', 'diffuse_web' => true, 'title' => 'Dessins',
        'legacy_payload' => ['us_pf_clas2015_visuel_top1' => 'top1.jpg', 'us_pf_clas2015_visuel_top2' => 'deleted', 'us_pf_clas2015_visuel_accueil' => 'accueil.jpg'],
        'theme_texts' => ['mdl_2015_classique' => ['cont_acceuil_bas' => '<p>Bienvenue chez Clara</p>']],
    ]);

    foreach (['Affiches', 'Carnets'] as $i => $nom) {
        $galerie = $this->book->galleries()->create(['name' => $nom, 'status' => 'published', 'position' => $i]);
        foreach (range(1, 5) as $n) {
            $galerie->media()->create(['user_id' => $this->book->id, 'filename' => "{$nom}{$n}.jpg", 'status' => 'published',
                'title' => "{$nom} {$n}", 'width' => 1200, 'height' => 800]);
        }
    }
});

function urlClas(string $chemin = '/'): string
{
    return 'https://clara-clas.'.config('ubdf.book_domain').$chemin;
}

it('rend l accueil : bandeau, grand visuel et texte, sans jQuery', function () {
    $html = $this->get(urlClas())->assertOk()
        ->assertSee('modele-responsive', false)
        ->assertDontSee('jquery', false)
        ->assertDontSee('fotorama', false)
        ->getContent();

    expect($html)->toContain('src="/books/clara-clas/source/top1.jpg"')
        // Visuel retire : plus d'image ; visuel jamais depose : image du modele.
        ->not->toContain('top2')
        ->toContain('/2012_web/classique2015/img/top_3.gif')
        ->toContain('src="/books/clara-clas/source/accueil.jpg"')
        ->toContain('Bienvenue chez Clara');
});

it('ouvre /portfolio sur la premiere rubrique, vignettes dans la colonne', function () {
    $html = $this->get(urlClas('/portfolio'))->assertOk()->getContent();

    expect($html)->toContain('x-data="diaporama(5)"')
        ->toContain('src="/books/clara-clas/source/Affiches1.jpg"')
        ->toContain("\$dispatch('diaporama-voir', 4)")
        ->not->toContain("\$dispatch('diaporama-voir', 5)");
});

it('rend la Bio et le contact', function () {
    $this->get(urlClas('/actualites'))->assertOk()->assertSee('modele-responsive', false);
    $this->get(urlClas('/contact'))->assertOk()->assertSee('name="fm_contact_mail"', false);
});

it('accepte le texte d accueil dans les reglages', function () {
    $this->actingAs($this->book)->postJson(urlClas('/reglages'), ['cle' => 'texte.cont_acceuil_bas', 'valeur' => '<p>Nouveau</p>'])->assertOk();

    expect($this->book->bookSetting->fresh()->theme_texts['mdl_2015_classique']['cont_acceuil_bas'])->toContain('Nouveau');
});
