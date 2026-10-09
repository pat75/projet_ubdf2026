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
        ->callTableColumnAction('lien_motcles', $this->creatif);

    expect(view('filament.recherche.detail-motcles', [
        'medias' => $this->creatif->media()->with('tags')->get(),
    ])->render())->toContain('Renard')->toContain('renard')->toContain('fox');
});

it('retire tous les mots-cles d un creatif sans toucher aux autres', function () {
    $autre = User::factory()->create();
    $autre->media()->create(['filename' => 'a.jpg', 'status' => 'published', 'analysed_at' => now()])
        ->tags()->attach(Tag::create(['label' => 'chat', 'lang' => 'fr'])->id);

    Livewire::test(AnalyseIA::class)
        ->callTableAction('retirerMotsCles', $this->creatif)
        ->assertNotified('2 mots-clés retirés');

    expect(\DB::table('media_tag')->count())->toBe(1)
        ->and($this->creatif->media()->first()->analysed_at)->not->toBeNull();
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

it('montre sur la page NVIDIA l IA des dernieres analyses, avec date et heure', function () {
    $this->creatif->media()->create(['filename' => 'n.jpg', 'status' => 'published', 'ai_model' => \App\Services\IA\Nvidia::MODELES_VISION[0], 'analysed_at' => '2026-10-09 14:42:33']);
    $this->creatif->media()->create(['filename' => 'o.jpg', 'status' => 'published', 'ai_model' => 'openai/gpt-4o-mini', 'analysed_at' => '2026-10-09 14:50:00']);

    $this->get('/admin_/reglage-nvidia')->assertOk()
        ->assertSeeInOrder(['Dernières analyses', '09/10/2026 16:50:00', 'OpenRouter', 'openai/gpt-4o-mini', '09/10/2026 16:42:33', 'NVIDIA']);
});
