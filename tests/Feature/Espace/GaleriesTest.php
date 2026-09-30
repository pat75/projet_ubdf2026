<?php

use App\Livewire\Espace\Galeries;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->creatif = User::factory()->create(['login' => 'testespace']);
    $this->actingAs($this->creatif);
});

afterEach(function () {
    File::deleteDirectory(App\Support\DossierBook::chemin('testespace'));
});

function galerie(User $u, array $attrs = []): Gallery
{
    static $n = 0;
    $n++;

    return $u->galleries()->create($attrs + ['name' => 'G'.$n, 'slug' => 'g'.$n, 'status' => 'published', 'position' => $n]);
}

it('liste les galeries du creatif', function () {
    galerie($this->creatif, ['name' => 'Affiches']);

    $this->get(route('espace.galeries'))->assertOk()->assertSee('Affiches');
});

it('cree un portfolio numerote avec un slug unique', function () {
    galerie($this->creatif, ['name' => 'Portfolio 2', 'slug' => 'portfolio-2']);

    Livewire::test(Galeries::class)->call('creer')->assertHasNoErrors();

    expect($this->creatif->galleries()->pluck('slug')->all())->toContain('portfolio-2-2');
});

it('renomme, masque et reordonne les portfolios', function () {
    $a = galerie($this->creatif);
    $b = galerie($this->creatif);

    Livewire::test(Galeries::class)
        ->call('enregistrerChamp', 'portfolio-'.$a->id, 'Nouveau nom')
        ->call('basculerPublication', $a->id)
        ->call('ordonner', [$b->id, $a->id]);

    expect($a->fresh())->name->toBe('Nouveau nom')->status->toBe('draft')->position->toBe(2)
        ->and($b->fresh()->position)->toBe(1);
});

it('refuse de toucher la galerie d un autre creatif', function () {
    $autre = galerie(User::factory()->create());
    $visuel = $autre->media()->create(['user_id' => $autre->user_id, 'filename' => 'x.jpg', 'status' => 'published']);

    Livewire::test(Galeries::class)->call('supprimer', $autre->id)->assertForbidden();
    Livewire::test(Galeries::class)->call('supprimerVisuel', $visuel->id)->assertNotFound();
    Livewire::test(Galeries::class)->call('enregistrerChamp', 'titre-'.$visuel->id, 'Pirate')->assertStatus(422);
    $this->get(route('espace.galeries.show', $autre))->assertForbidden();

    expect($autre->fresh()->trashed())->toBeFalse()
        ->and($visuel->fresh()->title)->toBeNull();
});

it('renvoie l ancien ecran d une galerie vers son portfolio', function () {
    $g = galerie($this->creatif);

    $this->get(route('espace.galeries.show', $g))->assertRedirect(route('espace.galeries').'#portfolio-'.$g->id);
});

it('supprime une galerie et ses visuels', function () {
    $g = galerie($this->creatif);
    $g->media()->create(['user_id' => $this->creatif->id, 'filename' => 'a.jpg', 'status' => 'published', 'size' => 100]);

    Livewire::test(Galeries::class)->call('supprimer', $g->id);

    expect($g->fresh()->trashed())->toBeTrue()
        ->and(Media::where('gallery_id', $g->id)->count())->toBe(0);
});

it('enregistre un visuel envoye dans le portfolio choisi, borne a la taille source', function () {
    galerie($this->creatif);
    $g = galerie($this->creatif);

    Livewire::test(Galeries::class)
        ->set('cible', $g->id)
        ->set('fichiers', [UploadedFile::fake()->image('Portrait.jpg', 3000, 1000)])
        ->assertHasNoErrors();

    $visuel = $g->media()->sole();

    expect($visuel->title)->toBe('Portrait')
        ->and($visuel->width)->toBe(1980)
        ->and(is_file(App\Support\DossierBook::chemin('testespace', $visuel->filename)))->toBeTrue()
        ->and($this->creatif->fresh()->media_count)->toBe(1);
});

it('refuse un fichier qui n est pas une image', function () {
    $g = galerie($this->creatif);

    Livewire::test(Galeries::class)
        ->set('fichiers', [UploadedFile::fake()->create('script.php', 10, 'text/plain')])
        ->assertHasErrors('fichiers.0');

    expect($g->media()->count())->toBe(0);
});

it('range l ordre des visuels en identifiants legacy et edite leurs textes', function () {
    $g = galerie($this->creatif);
    $repris = $g->media()->create(['user_id' => $this->creatif->id, 'legacy_id' => 900, 'filename' => 'a.jpg', 'status' => 'published']);
    $nouveau = $g->media()->create(['user_id' => $this->creatif->id, 'filename' => 'b.jpg', 'status' => 'published']);

    Livewire::test(Galeries::class)
        ->call('ordonnerVisuels', $g->id, [$nouveau->id, $repris->id])
        ->call('enregistrerChamp', 'titre-'.$repris->id, 'Titre')
        ->call('enregistrerChamp', 'legende-'.$repris->id, "Ligne 1\nLigne 2")
        ->assertSeeInOrder(['b.jpg', 'a.jpg']);

    expect($g->fresh()->media_order)->toBe([(string) $nouveau->id, '900'])
        ->and($repris->fresh())->title->toBe('Titre')->description->toBe("Ligne 1\nLigne 2");
});

it('fait passer un visuel d un portfolio a l autre', function () {
    $depart = galerie($this->creatif);
    $arrivee = galerie($this->creatif);
    $a = $depart->media()->create(['user_id' => $this->creatif->id, 'legacy_id' => 901, 'filename' => 'a.jpg', 'status' => 'published']);
    $b = $depart->media()->create(['user_id' => $this->creatif->id, 'filename' => 'b.jpg', 'status' => 'published']);
    $c = $arrivee->media()->create(['user_id' => $this->creatif->id, 'filename' => 'c.jpg', 'status' => 'published']);
    $depart->update(['media_order' => ['901', (string) $b->id]]);

    Livewire::test(Galeries::class)->call('deplacerVisuel', $a->id, $arrivee->id, [$c->id, $a->id]);

    expect($a->fresh()->gallery_id)->toBe($arrivee->id)
        ->and($depart->fresh()->media_order)->toBe([(string) $b->id])
        ->and($arrivee->fresh()->media_order)->toBe([(string) $c->id, '901']);
});

it('ajoute une video YouTube avec sa vignette et son titre', function () {
    $g = galerie($this->creatif);
    $vignette = UploadedFile::fake()->image('v.jpg', 1280, 720);

    Http::fake([
        'www.youtube.com/oembed*' => Http::response(['title' => 'Mon clip']),
        'i.ytimg.com/*' => Http::response(file_get_contents($vignette->getRealPath())),
    ]);

    Livewire::test(Galeries::class)
        ->set('cible', $g->id)
        ->call('ajouterVideo', 'https://youtu.be/dQw4w9WgXcQ?si=abc')
        ->assertReturned(['ok' => true]);

    $video = $g->media()->sole();

    expect($video->title)->toBe('Mon clip')
        ->and($video->video_url)->toBe('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
        ->and($video->width)->toBe(1280)
        ->and(is_file(App\Support\DossierBook::chemin('testespace', $video->filename)))->toBeTrue()
        ->and($this->creatif->fresh()->media_count)->toBe(1);
});

it('refuse un lien qui n est pas une video, ou une video privee', function () {
    $g = galerie($this->creatif);
    Http::fake(['vimeo.com/api/oembed.json*' => Http::response([], 404)]);

    Livewire::test(Galeries::class)
        ->set('cible', $g->id)
        ->call('ajouterVideo', 'https://example.com/film')
        ->assertReturned(['erreur' => 'Ce lien n’est pas une vidéo YouTube ou Vimeo.'])
        ->call('ajouterVideo', 'https://vimeo.com/76979871')
        ->assertReturned(['erreur' => 'Vidéo introuvable ou privée sur Vimeo.']);

    expect($g->media()->count())->toBe(0);
});

it('remplace l image d un visuel sans toucher a ses textes', function () {
    $g = galerie($this->creatif);

    $test = Livewire::test(Galeries::class)->set('cible', $g->id)
        ->set('fichiers', [UploadedFile::fake()->image('avant.jpg', 400, 300)]);
    $visuel = $g->media()->sole();
    $visuel->update(['title' => 'Mon titre']);
    $poids = $this->creatif->fresh()->storage_used - $visuel->size;

    $test->set('remplace', $visuel->id)
        ->set('remplacement', UploadedFile::fake()->image('apres.png', 800, 600))
        ->assertHasNoErrors();

    $visuel->refresh();

    expect($visuel->title)->toBe('Mon titre')
        ->and($visuel->width)->toBe(800)
        ->and($visuel->mime)->toBe('image/png')
        ->and($this->creatif->fresh())->media_count->toBe(1)->storage_used->toBe($poids + $visuel->size);
});

it('protege un portfolio par un mot de passe relisible, puis le retire', function () {
    $g = galerie($this->creatif);

    Livewire::test(Galeries::class)
        ->call('definirMotDePasse', $g->id, 'abc')
        ->assertReturned(['erreur' => 'Le champ mot de passe doit contenir au moins 4 caractères.'])
        ->call('definirMotDePasse', $g->id, ' client2026 ')
        ->assertReturned(['ok' => true]);

    expect($g->fresh()->password)->toBe('client2026')
        ->and(DB::table('galleries')->where('id', $g->id)->value('password'))->not->toBe('client2026');

    Livewire::test(Galeries::class)->call('definirMotDePasse', $g->id, '');

    expect($g->fresh()->estProtegee())->toBeFalse();
});

it('refuse un visuel au-dela du plafond de la formule', function () {
    config(['formules.limites.gratuite.visuels' => 1]);
    $this->creatif->update(['plan' => 0, 'media_count' => 1]);
    $galerie = galerie($this->creatif);

    Livewire::test(Galeries::class)
        ->set('cible', $galerie->id)
        ->set('fichiers', [UploadedFile::fake()->image('Portrait.jpg', 300, 200)])
        ->assertHasErrors('fichiers');

    expect($galerie->media()->count())->toBe(0);
});
