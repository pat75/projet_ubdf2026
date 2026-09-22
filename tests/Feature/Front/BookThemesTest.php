<?php

use App\Models\Conversation;
use App\Models\User;
use App\Services\Book\ContexteBook;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();

    $this->book = User::factory()->create(['login' => 'aurelie-b', 'firstname' => 'Aurélie', 'lastname' => 'B.']);
    $this->book->bookSetting()->create(['title' => 'Aurélie B.', 'theme' => 'mdl_2016_zoom', 'diffuse_web' => true]);

    $galerie = $this->book->galleries()->create(['name' => 'Illustrations', 'status' => 'published', 'position' => 1]);
    foreach ([3, 1, 2] as $n) {
        $galerie->media()->create(['user_id' => $this->book->id, 'filename' => "v{$n}.jpg", 'status' => 'published', 'title' => "Visuel {$n}"]);
    }

    $bio = $this->book->sections()->create(['kind' => 'pages', 'title' => 'Bio', 'is_published' => true, 'position' => 1]);
    $bio->articles()->create(['user_id' => $this->book->id, 'title' => 'Parcours', 'body' => '<p>Diplômée en 2010</p>', 'status' => 'published']);
});

function urlBook(string $chemin = '/'): string
{
    return 'https://aurelie-b.'.config('ubdf.book_domain').$chemin;
}

it('rend le book dans son theme d origine', function () {
    $this->get(urlBook())
        ->assertOk()
        ->assertSee('/2012_web/zoom2016/_/css/mdl_zoom.css', false)
        ->assertSee('v1.jpg', false);
});

it('sert les URL du legacy', function () {
    $galerie = $this->book->galleries()->first();
    $page = $this->book->articles()->first();
    $bio = $this->book->sections()->first();

    $this->get(urlBook('/illustrations-p'.$galerie->id))->assertOk();
    $this->get(urlBook('/parcours-r'.$bio->id.'-c'.$page->id))->assertOk()->assertSee('Diplômée en 2010', false);
    $this->get(urlBook('/contact'))->assertOk()->assertSee('fm_contact_message', false);
});

it('rend chacun des dix gabarits sans erreur', function () {
    foreach (array_keys(config('book_themes')) as $theme) {
        $this->book->bookSetting->update(['theme' => $theme]);

        foreach (['/', '/portfolio', '/news', '/contact'] as $page) {
            $this->get(urlBook($page))->assertOk();
        }
    }
})->skip(fn () => count(glob(resource_path('views/book/themes/*'), GLOB_ONLYDIR)) < 9, 'themes pas encore tous portes');

it('ordonne les visuels comme usbook2011_img_ordre', function () {
    $elements = [['img_id' => 10], ['img_id' => 20], ['img_id' => 30], ['img_id' => 40]];

    // 30 et 10 sont listes ; 20 et 40 ne le sont pas : ils tombent sur la
    // meme cle vide, seul le dernier survit, en tete.
    $ordonnes = array_values(ContexteBook::ordonner($elements, [30, 10]));

    expect(array_column($ordonnes, 'img_id'))->toBe([40, 30, 10]);
});

it('transmet le formulaire de contact du book a la messagerie', function () {
    $this->post(urlBook('/contact'), [
        'fm_contact_nom_prenom' => 'Jean Client',
        'fm_contact_mail' => 'jean@example.com',
        'fm_contact_message' => 'Bonjour, une commande pour la rentrée ?',
    ])->assertOk()->assertJson(['error' => false]);

    expect(Conversation::where('user_id', $this->book->id)->count())->toBe(1);
});

it('renvoie les erreurs dans la forme du formulaire d origine', function () {
    $this->post(urlBook('/contact'), ['fm_contact_mail' => 'pas-une-adresse'])
        ->assertOk()
        ->assertJsonStructure(['errors']);
});

it('sert le gabarit non diffuse a un visiteur, le theme a son proprietaire', function () {
    $this->book->bookSetting->update(['diffuse_web' => false]);

    $this->get(urlBook())->assertOk()->assertSee('2012_web/non_diffuse', false)
        ->assertDontSee('mdl_zoom.css', false);

    $this->actingAs($this->book)->get(urlBook())->assertOk()->assertSee('mdl_zoom.css', false);
});

it('rend l accueil de Pinter en mosaique de tout le portfolio', function () {
    $this->book->bookSetting->update(['theme' => 'mdl_2013_pinter']);

    $this->get(urlBook())->assertOk()->assertSee('v1.jpg', false)->assertSee('fancybox', false);
});
