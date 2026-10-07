<?php

use App\Filament\Pages\Recherche\AnalyseIA;
use App\Filament\Pages\Recherche\DernieresRecherches;
use App\Jobs\AnalyserMedia;
use App\Models\Admin;
use App\Models\SearchQuery;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'secret', 'is_active' => true]), 'admin');

    $this->creatif = User::factory()->create(['login' => 'nolwenn', 'in_home_selection' => true]);
    $this->creatif->bookSetting()->create(['allow_ai_analysis' => true]);
    $media = $this->creatif->media()->create(['filename' => 'v.jpg', 'status' => 'published', 'ai_title' => 'Renard', 'analysed_at' => now()]);
    $media->tags()->attach([Tag::create(['label' => 'renard', 'lang' => 'fr'])->id, Tag::create(['label' => 'fox', 'lang' => 'en'])->id]);
});

it('ouvre les deux pages de la rubrique Recherche', function () {
    $this->get('/admin_/recherche/analyse-ia')->assertOk()->assertSee('Images analysées')->assertSee('nolwenn');
    $this->get('/admin_/recherche/dernieres')->assertOk();
});

it('liste les creatifs analyses avec leurs compteurs et le detail des mots-cles', function () {
    Livewire::test(AnalyseIA::class)
        ->assertCanSeeTableRecords([$this->creatif])
        ->assertTableColumnStateSet('nb_motcles', 2, $this->creatif)
        ->assertTableActionExists('detail');

    expect(view('filament.recherche.detail-motcles', [
        'medias' => $this->creatif->media()->with('tags')->get(),
    ])->render())->toContain('Renard')->toContain('renard')->toContain('fox');
});

it('lance un lot depuis la page', function () {
    Bus::fake();
    $this->creatif->media()->create(['filename' => 'w.jpg', 'status' => 'published']);

    Livewire::test(AnalyseIA::class)->callAction('lancer')->assertNotified('1 image analysée');

    Bus::assertDispatchedSyncTimes(AnalyserMedia::class, 1);
});

it('filtre les recherches sans resultat', function () {
    $vaine = SearchQuery::create(['q' => 'dinosaure', 'brand' => 'ub', 'nb_books' => 0, 'nb_images' => 0, 'result_user_ids' => []]);
    $utile = SearchQuery::create(['q' => 'renard', 'brand' => 'ub', 'nb_books' => 1, 'nb_images' => 1, 'result_user_ids' => [$this->creatif->id]]);

    Livewire::test(DernieresRecherches::class)
        ->assertCanSeeTableRecords([$vaine, $utile])
        ->assertSee('nolwenn')
        ->filterTable('sans_resultat')
        ->assertCanSeeTableRecords([$vaine])
        ->assertCanNotSeeTableRecords([$utile]);
});
