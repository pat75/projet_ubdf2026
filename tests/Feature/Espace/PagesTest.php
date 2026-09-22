<?php

use App\Livewire\Espace\EditeurPage;
use App\Livewire\Espace\Pages;
use App\Models\BookSection;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->creatif = User::factory()->create();
    $this->actingAs($this->creatif);
    $this->rubrique = $this->creatif->sections()->create([
        'kind' => BookSection::PAGES, 'title' => 'Bio', 'slug' => 'bio', 'is_published' => true, 'position' => 1,
    ]);
});

it('liste les rubriques et leurs pages', function () {
    $this->rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'Parcours', 'status' => 'published']);

    $this->get(route('espace.pages'))->assertOk()->assertSee('Bio')->assertSee('Parcours');
});

it('cree une rubrique puis une page en brouillon', function () {
    Livewire::test(Pages::class)->set('nom', 'Presse')->call('creerRubrique')->assertHasNoErrors();

    $presse = $this->creatif->sections()->where('title', 'Presse')->sole();

    Livewire::test(Pages::class)
        ->set('nouvellePage.'.$presse->id, 'Article Libé')
        ->call('creerPage', $presse->id)
        ->assertRedirect();

    expect($presse->articles()->sole())->title->toBe('Article Libé')->status->toBe('draft');
});

it('nettoie le HTML enregistre', function () {
    $page = $this->rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'Parcours', 'status' => 'draft']);

    Livewire::test(EditeurPage::class, ['page' => $page])
        ->set('corps', '<p onclick="x()">Bonjour <strong>à tous</strong></p><script>alert(1)</script><a href="javascript:alert(1)">lien</a>')
        ->set('enLigne', true)
        ->call('enregistrer')
        ->assertRedirect(route('espace.pages'));

    $page->refresh();

    expect($page->body)->toContain('<strong>à tous</strong>')
        ->not->toContain('script')->not->toContain('onclick')->not->toContain('javascript')
        ->and($page->status)->toBe('published');
});

it('range l ordre des pages en identifiants legacy', function () {
    $a = $this->rubrique->articles()->create(['user_id' => $this->creatif->id, 'legacy_id' => 77, 'title' => 'A', 'status' => 'published']);
    $b = $this->rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'B', 'status' => 'published']);

    Livewire::test(Pages::class)->call('ordonnerPages', $this->rubrique->id, [$b->id, $a->id]);

    expect($this->rubrique->fresh()->page_order)->toBe([(string) $b->id, '77']);
});

it('interdit les pages d un autre creatif', function () {
    $autre = User::factory()->create();
    $rubrique = $autre->sections()->create(['kind' => BookSection::PAGES, 'title' => 'X', 'slug' => 'x', 'is_published' => true, 'position' => 1]);
    $page = $rubrique->articles()->create(['user_id' => $autre->id, 'title' => 'Secret', 'status' => 'draft']);

    $this->get(route('espace.pages.edit', $page))->assertForbidden();
    Livewire::test(Pages::class)->call('supprimerRubrique', $rubrique->id)->assertForbidden();
});
