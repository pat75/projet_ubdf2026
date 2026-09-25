<?php

use App\Livewire\Espace\EditeurPage;
use App\Livewire\Espace\Pages;
use App\Models\BookArticle;
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

it('affiche la liste des pages avec l editeur par blocs sans erreur', function () {
    config(['pages.editeur_texte' => 'redactor_bloc']);
    $this->rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'Parcours', 'status' => 'published']);

    $this->get(route('espace.pages'))->assertOk()->assertSee('Parcours');
});

it('ouvre une page avec l editeur par blocs charge cote JS', function () {
    config(['pages.editeur_texte' => 'redactor_bloc']);
    $page = $this->rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'Parcours', 'status' => 'draft']);

    Livewire::test(Pages::class)
        ->call('ouvrirPage', $page->id)
        ->assertSee('espacePageEditorBlocs')
        ->assertDontSee('espacePageEditor(');
});

it('cree une rubrique puis une page en brouillon, ouverte aussitot', function () {
    Livewire::test(Pages::class)->call('creerRubrique')->assertHasNoErrors();

    $presse = $this->creatif->sections()->where('title', '')->sole();
    expect($presse->is_published)->toBeFalse();

    Livewire::test(Pages::class)->call('enregistrerChamp', 'rubrique-'.$presse->id, 'Presse');
    expect($presse->refresh())->title->toBe('Presse')->is_published->toBeTrue();

    Livewire::test(Pages::class)
        ->call('creerPage', $presse->id)
        ->assertHasNoErrors()
        ->assertSet('edition', $presse->articles()->sole()->id);

    expect($presse->articles()->sole())->title->toBe('')->status->toBe('draft');

    Livewire::test(Pages::class)->call('ouvrirPage', $presse->articles()->sole()->id)
        ->set('corps', '<p>Texte</p>')->call('enregistrerPage');

    expect($presse->articles()->sole()->status)->toBe('published');
});

it('bascule le moteur d edition en developpement, jamais ailleurs', function () {
    app()->detectEnvironment(fn () => 'local');
    config(['pages.editeur_texte' => 'redactor']);

    Livewire::test(Pages::class)
        ->assertSee('Redactor 3.5.2 classique')
        ->call('basculerEditeur')
        ->assertRedirect(route('espace.pages'));

    Livewire::test(Pages::class)->assertSee('Redactor bloc (Editor.js)');

    app()->detectEnvironment(fn () => 'production');
    Livewire::test(Pages::class)->assertDontSee('dev_only', false)->call('basculerEditeur')->assertForbidden();
});

it('deplie une page pour l editer, et la replie au second clic', function () {
    $page = $this->rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'Parcours', 'body' => '<p>Avant</p>', 'status' => 'draft']);

    Livewire::test(Pages::class)
        ->call('ouvrirPage', $page->id)
        ->assertSet('edition', $page->id)
        ->assertSet('corps', '<p>Avant</p>')
        ->call('ouvrirPage', $page->id)
        ->assertSet('edition', null);
});

it('enregistre le contenu Redactor d une page depliee', function () {
    $page = $this->rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'Parcours', 'status' => 'draft']);

    Livewire::test(Pages::class)
        ->call('ouvrirPage', $page->id)
        ->set('corps', '<p onclick="x()">Bonjour <strong>à tous</strong></p><script>alert(1)</script>')
        ->call('enregistrerPage')
        ->assertHasNoErrors();

    expect($page->fresh()->body)->toContain('<strong>à tous</strong>')->not->toContain('script')->not->toContain('onclick');
});

it('renomme une page sur place', function () {
    $page = $this->rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'Parcours', 'status' => 'draft']);

    Livewire::test(Pages::class)->call('enregistrerChamp', 'page-'.$page->id, 'Nouveau titre')->assertReturned(['ok' => true]);

    expect($page->fresh())->title->toBe('Nouveau titre')->slug->toBe('nouveau-titre');
});

it('supprime une page et replie l edition en cours', function () {
    $page = $this->rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'Parcours', 'status' => 'draft']);

    Livewire::test(Pages::class)
        ->call('ouvrirPage', $page->id)
        ->call('supprimerPage', $page->id)
        ->assertSet('edition', null);

    expect(BookArticle::find($page->id))->toBeNull();
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

it('enregistre les blocs Editor.js et derive le HTML equivalent', function () {
    config(['pages.editeur_texte' => 'redactor_bloc']);
    $page = $this->rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'Parcours', 'status' => 'draft']);

    Livewire::test(Pages::class)
        ->call('ouvrirPage', $page->id)
        ->set('blocs', [
            ['type' => 'header', 'data' => ['text' => 'Un titre', 'level' => 2]],
            ['type' => 'paragraph', 'data' => ['text' => 'Un paragraphe <script>alert(1)</script>avec du <b>gras</b>.']],
            ['type' => 'image', 'data' => ['file' => ['url' => 'https://exemple.test/photo.jpg'], 'caption' => 'Légende']],
        ])
        ->call('enregistrerBlocs')
        ->assertHasNoErrors();

    $page->refresh();

    expect($page->body_blocks)->toHaveCount(3)
        ->and($page->body)->toContain('<h2>Un titre</h2>')
        ->toContain('<b>gras</b>')
        ->not->toContain('script')
        ->toContain('<img src="https://exemple.test/photo.jpg"')
        ->toContain('<figcaption>Légende</figcaption>');
});

it('recharge les blocs existants a l ouverture d une page', function () {
    $blocs = [['type' => 'paragraph', 'data' => ['text' => 'Déjà là']]];
    $page = $this->rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'Parcours', 'body_blocks' => $blocs, 'status' => 'draft']);

    Livewire::test(Pages::class)->call('ouvrirPage', $page->id)->assertSet('blocs', $blocs);
});

it('interdit les pages d un autre creatif', function () {
    $autre = User::factory()->create();
    $rubrique = $autre->sections()->create(['kind' => BookSection::PAGES, 'title' => 'X', 'slug' => 'x', 'is_published' => true, 'position' => 1]);
    $page = $rubrique->articles()->create(['user_id' => $autre->id, 'title' => 'Secret', 'status' => 'draft']);

    $this->get(route('espace.pages.edit', $page))->assertForbidden();
    Livewire::test(Pages::class)->call('supprimerRubrique', $rubrique->id)->assertForbidden();
});

it('place une nouvelle rubrique en tete de liste', function () {
    Livewire::test(Pages::class)->call('creerRubrique');

    $premiere = $this->creatif->sections()->orderBy('position')->orderBy('id')->first();
    expect($premiere->title)->toBe('');
});

it('ne supprime une rubrique que vide', function () {
    $this->rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'Parcours', 'status' => 'published']);

    Livewire::test(Pages::class)
        ->assertSee('Supprimez-la d’abord', false)
        ->call('supprimerRubrique', $this->rubrique->id);
    expect($this->rubrique->fresh())->not->toBeNull();

    $this->rubrique->articles()->delete();
    Livewire::test(Pages::class)->call('supprimerRubrique', $this->rubrique->id);
    expect($this->rubrique->fresh())->toBeNull();
});

it('reecrit les anciennes URL d images a l ouverture dans l editeur', function () {
    $page = $this->rubrique->articles()->create(['user_id' => $this->creatif->id, 'title' => 'Parcours', 'status' => 'published',
        'body' => '<p><img src="https://www.ultra-book.com/users_2/a/d/adolie/img_cms/images/3c97.jpg"><img src="/users_2/a/d/o/adolie/img_cms/images/3c98.jpg"></p>']);

    Livewire::test(Pages::class)->call('ouvrirPage', $page->id)
        ->assertSet('corps', '<p><img src="/books/adolie/cms/images/3c97.jpg"><img src="/books/adolie/cms/images/3c98.jpg"></p>');
});
