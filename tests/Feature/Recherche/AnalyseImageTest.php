<?php

use App\Actions\Recherche\LancerAnalyseLot;
use App\Jobs\AnalyserMedia;
use App\Models\Media;
use App\Models\User;
use App\Services\IA\AnalyseImage;
use App\Services\Images\GenerateurImages;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Bus;

function creatifAnalysable(array $attributs = [], bool $accord = true): User
{
    $creatif = User::factory()->create($attributs + ['in_home_selection' => true]);
    $creatif->bookSetting()->create(['allow_ai_analysis' => $accord]);

    return $creatif;
}

function visuel(User $creatif): Media
{
    return $creatif->media()->create(['filename' => 'v'.uniqid().'.jpg', 'status' => 'published']);
}

it('normalise la reponse du modele', function () {
    $r = AnalyseImage::lireReponse("```json\n".json_encode([
        'titre' => 'Renard dans la neige',
        'description' => 'Un renard roux.',
        'tags_fr' => ['Renard', 'renard', '  Aquarelle ', 'hiver', 'jeunesse'],
        'tags_en' => ['Fox'],
    ])."\n```");

    expect($r['tags_fr'])->toBe(['renard', 'aquarelle', 'hiver', 'jeunesse'])
        ->and($r['tags_en'])->toBe(['fox']);
});

it('refuse une reponse illisible ou trop pauvre', function (string $contenu) {
    AnalyseImage::lireReponse($contenu);
})->throws(RuntimeException::class)->with([
    'pas du json' => 'Voici un renard.',
    'deux mots-cles' => json_encode(['titre' => 'x', 'tags_fr' => ['a', 'b']]),
]);

it('enregistre titre, description et mots-cles du visuel', function () {
    config(['services.openrouter.api_key' => 'test-key']);
    $fichier = tempnam(sys_get_temp_dir(), 'img');
    file_put_contents($fichier, 'jpeg');
    $this->mock(GenerateurImages::class)->shouldReceive('produire')->andReturn($fichier);
    Http::fake(['openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => json_encode([
        'titre' => 'Renard', 'description' => 'Un renard.', 'tags_fr' => ['renard', 'hiver', 'aquarelle'], 'tags_en' => ['fox'],
    ])]]]])]);

    $media = visuel(creatifAnalysable());
    (new AnalyserMedia($media))->handle(app(AnalyseImage::class));

    $media->refresh();
    expect($media->ai_status)->toBe('ok')
        ->and($media->analysed_at)->not->toBeNull()
        ->and($media->tags->pluck('label')->sort()->values()->all())->toBe(['aquarelle', 'fox', 'hiver', 'renard']);
});

it("n'appelle pas l'IA si le creatif a retire son accord", function () {
    Http::fake();

    $media = visuel(creatifAnalysable(accord: false));
    (new AnalyserMedia($media))->handle(app(AnalyseImage::class));

    Http::assertNothingSent();
    expect($media->fresh()->analysed_at)->toBeNull();
});

it('met en file les visuels eligibles seulement, par lot', function () {
    Bus::fake();

    $selectionne = creatifAnalysable();
    $payant = creatifAnalysable(['in_home_selection' => false, 'plan' => 1, 'plan_started_at' => now()->subMonth(), 'plan_months' => 12]);
    $echu = creatifAnalysable(['in_home_selection' => false, 'plan' => 1, 'plan_started_at' => now()->subYears(2), 'plan_months' => 12]);
    $sansAccord = creatifAnalysable(accord: false);

    foreach ([$selectionne, $payant, $echu, $sansAccord] as $c) {
        visuel($c);
    }
    visuel($selectionne)->forceFill(['analysed_at' => now()])->save();

    expect((new LancerAnalyseLot)())->toBe(['ok' => 2, 'erreurs' => 0]);
    Bus::assertDispatchedSyncTimes(AnalyserMedia::class, 2);

    foreach (range(1, 12) as $i) {
        visuel($selectionne);
    }
    expect((new LancerAnalyseLot)()['ok'])->toBe(LancerAnalyseLot::TAILLE);
});

it("efface les resultats de l'analyse quand le creatif retire son accord", function () {
    $creatif = creatifAnalysable();
    $media = visuel($creatif);
    $media->forceFill(['ai_title' => 'Renard', 'ai_status' => 'ok', 'analysed_at' => now()])->save();
    $media->tags()->attach(App\Models\Tag::create(['label' => 'renard', 'lang' => 'fr']));

    Livewire\Livewire::actingAs($creatif)->test(App\Livewire\Espace\Diffusion::class)
        ->assertSet('analyse', true)
        ->call('basculer', 'analyse')
        ->assertSet('analyse', false);

    expect($creatif->bookSetting->fresh()->allow_ai_analysis)->toBeFalse()
        ->and($media->fresh()->analysed_at)->toBeNull()
        ->and($media->tags()->count())->toBe(0);
});

it("n'ecrit rien si l'accord est retire pendant l'appel a l'IA", function () {
    config(['services.openrouter.api_key' => 'test-key']);
    $fichier = tempnam(sys_get_temp_dir(), 'img');
    file_put_contents($fichier, 'jpeg');
    $this->mock(GenerateurImages::class)->shouldReceive('produire')->andReturn($fichier);
    $media = visuel($creatif = creatifAnalysable());

    Http::fake(function () use ($creatif) {
        $creatif->bookSetting->update(['allow_ai_analysis' => false]);

        return Http::response(['choices' => [['message' => ['content' => json_encode([
            'titre' => 'Renard', 'tags_fr' => ['renard', 'hiver', 'aquarelle'],
        ])]]]]);
    });

    app(AnalyseImage::class)->analyser($media);

    expect($media->fresh()->analysed_at)->toBeNull()
        ->and($media->tags()->count())->toBe(0);
});

it("ne compte pas la formule gratuite comme payante", function () {
    Bus::fake();
    visuel(creatifAnalysable(['in_home_selection' => false, 'plan' => 0, 'plan_expires_at' => now()->addYear()]));

    expect((new LancerAnalyseLot)())->toBe(['ok' => 0, 'erreurs' => 0]);
});

it("reserve l'analyse IA aux books en selection ou avec une formule", function () {
    $gratuit = User::factory()->create(['in_home_selection' => false, 'plan' => 0]);

    Livewire\Livewire::actingAs($gratuit)->test(App\Livewire\Espace\Diffusion::class)
        ->assertSee('Option réservée aux créatifs ayant souscrit une formule.')
        ->call('basculer', 'analyse')
        ->assertSet('analyse', false);

    expect($gratuit->bookSetting?->allow_ai_analysis)->toBeFalsy();

    Livewire\Livewire::actingAs(creatifAnalysable(accord: false))->test(App\Livewire\Espace\Diffusion::class)
        ->assertDontSee('Option réservée aux créatifs ayant souscrit une formule.')
        ->call('basculer', 'analyse')
        ->assertSet('analyse', true);
});
