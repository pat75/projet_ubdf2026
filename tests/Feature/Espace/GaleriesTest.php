<?php

use App\Livewire\Espace\Galeries;
use App\Livewire\Espace\Visuels;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

beforeEach(function () {
    $this->creatif = User::factory()->create(['login' => 'testespace']);
    $this->actingAs($this->creatif);
});

afterEach(function () {
    File::deleteDirectory(storage_path('app/public/books/testespace'));
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

it('cree une galerie avec un slug unique', function () {
    galerie($this->creatif, ['name' => 'Affiches', 'slug' => 'affiches']);

    Livewire::test(Galeries::class)->set('nom', 'Affiches')->call('creer')->assertHasNoErrors();

    expect($this->creatif->galleries()->pluck('slug')->all())->toContain('affiches-2');
});

it('renomme, masque et reordonne les galeries', function () {
    $a = galerie($this->creatif);
    $b = galerie($this->creatif);

    Livewire::test(Galeries::class)
        ->call('renommer', $a->id, 'Nouveau nom')
        ->call('basculerPublication', $a->id)
        ->call('ordonner', [$b->id, $a->id]);

    expect($a->fresh())->name->toBe('Nouveau nom')->status->toBe('draft')->position->toBe(2)
        ->and($b->fresh()->position)->toBe(1);
});

it('refuse de toucher la galerie d un autre creatif', function () {
    $autre = galerie(User::factory()->create());

    Livewire::test(Galeries::class)->call('supprimer', $autre->id)->assertForbidden();
    $this->get(route('espace.galeries.show', $autre))->assertForbidden();
    expect($autre->fresh()->trashed())->toBeFalse();
});

it('supprime une galerie et ses visuels', function () {
    $g = galerie($this->creatif);
    $g->media()->create(['user_id' => $this->creatif->id, 'filename' => 'a.jpg', 'status' => 'published', 'size' => 100]);

    Livewire::test(Galeries::class)->call('supprimer', $g->id);

    expect($g->fresh()->trashed())->toBeTrue()
        ->and(Media::where('gallery_id', $g->id)->count())->toBe(0);
});

it('enregistre un visuel envoye, borne a la taille source', function () {
    $g = galerie($this->creatif);

    Livewire::test(Visuels::class, ['galerie' => $g])
        ->set('fichiers', [UploadedFile::fake()->image('Portrait.jpg', 3000, 1000)])
        ->assertHasNoErrors();

    $visuel = $g->media()->sole();

    expect($visuel->title)->toBe('Portrait')
        ->and($visuel->width)->toBe(1980)
        ->and(is_file(storage_path('app/public/books/testespace/'.$visuel->filename)))->toBeTrue()
        ->and($this->creatif->fresh()->media_count)->toBe(1);
});

it('refuse un fichier qui n est pas une image', function () {
    $g = galerie($this->creatif);

    Livewire::test(Visuels::class, ['galerie' => $g])
        ->set('fichiers', [UploadedFile::fake()->create('script.php', 10, 'text/plain')])
        ->assertHasErrors('fichiers.0');

    expect($g->media()->count())->toBe(0);
});

it('range l ordre des visuels en identifiants legacy', function () {
    $g = galerie($this->creatif);
    $repris = $g->media()->create(['user_id' => $this->creatif->id, 'legacy_id' => 900, 'filename' => 'a.jpg', 'status' => 'published']);
    $nouveau = $g->media()->create(['user_id' => $this->creatif->id, 'filename' => 'b.jpg', 'status' => 'published']);

    Livewire::test(Visuels::class, ['galerie' => $g])
        ->call('ordonner', [$nouveau->id, $repris->id])
        ->call('modifier', $repris->id, 'title', 'Titre')
        ->assertSeeInOrder(['b.jpg', 'a.jpg']);

    expect($g->fresh()->media_order)->toBe([(string) $nouveau->id, '900'])
        ->and($repris->fresh()->title)->toBe('Titre');
});
