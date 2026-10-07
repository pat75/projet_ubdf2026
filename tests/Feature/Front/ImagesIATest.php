<?php

use App\Models\Media;
use App\Models\Tag;
use App\Models\User;

function creatifTague(string $login, bool $accord = true): User
{
    $user = User::factory()->create(['login' => $login, 'firstname' => ucfirst($login), 'lastname' => 'Créatif']);
    $user->bookSetting()->create(['diffuse_web' => true, 'diffuse_ub' => true, 'allow_ai_analysis' => $accord]);

    return $user;
}

function imageTaguee(User $user, string $titre, array $tags): Media
{
    $media = $user->media()->create(['filename' => uniqid().'.jpg', 'status' => 'published']);
    $media->forceFill(['ai_title' => $titre, 'ai_description' => 'Une description.', 'ai_status' => 'ok', 'analysed_at' => now()])->save();
    $media->tags()->attach(collect($tags)->map(fn ($label) => Tag::firstOrCreate(['label' => $label, 'lang' => 'fr'])->id));

    return $media;
}

it("affiche la page d'une image, non indexee, avec ses mots-cles et son createur", function () {
    $media = imageTaguee(creatifTague('adolie'), 'Affiche décorative', ['décoratif', 'affiche vintage']);

    $this->get(pageIA("/image/{$media->id}/affiche-decorative"))
        ->assertOk()
        ->assertSee('Affiche décorative')->assertSee('Une description.')
        ->assertSee('décoratif')->assertSee('/images/affiche-vintage')
        ->assertSee('Adolie Créatif')
        ->assertSee('noindex, follow', false);
});

it('redirige vers le bon slug', function () {
    $media = imageTaguee(creatifTague('adolie'), 'Affiche décorative', ['décoratif']);

    $this->get(pageIA("/image/{$media->id}/autre-chose"))->assertRedirectContains("/image/{$media->id}/affiche-decorative");
});

it("ne montre pas l'image sans accord du createur", function () {
    $media = imageTaguee(creatifTague('adolie', accord: false), 'Affiche', ['décoratif']);

    $this->get(pageIA("/image/{$media->id}/affiche"))->assertNotFound();
    $this->get(pageIA('/images/decoratif'))->assertNotFound();
});

it("n'indexe une page mot-cle qu'au-dela du seuil", function () {
    imageTaguee(creatifTague('adolie'), 'Renard', ['renard']);

    $this->get(pageIA('/images/renard'))->assertOk()->assertSee('Renard')->assertSee('noindex, follow', false);

    foreach (range(1, Tag::INDEXABLE_CREATIFS) as $i) {
        $creatif = creatifTague('creatif'.$i);
        foreach (range(1, (int) ceil(Tag::INDEXABLE_IMAGES / Tag::INDEXABLE_CREATIFS)) as $j) {
            imageTaguee($creatif, "Renard {$i}-{$j}", ['renard']);
        }
    }

    $this->get(pageIA('/images/renard'))->assertOk()
        ->assertDontSee('noindex, follow', false)
        ->assertSee('"@type":"CollectionPage"', false)
        ->assertSee('Creatif1 Créatif')->assertSee('rounded-full', false);

    $this->get(pageIA('/sitemap-pages.xml'))->assertOk()->assertSee('/images/renard');
});

function pageIA(string $chemin): string
{
    return 'https://'.config('ubdf.book_domain').$chemin;
}
