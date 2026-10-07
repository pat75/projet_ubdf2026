<?php

use App\Actions\Recherche\LancerAnalyseLot;
use App\Jobs\AnalyserMedia;
use App\Models\Media;
use App\Models\User;
use App\Services\IA\AnalyseImage;
use App\Services\Images\GenerateurImages;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

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
    Queue::fake();

    $selectionne = creatifAnalysable();
    $payant = creatifAnalysable(['in_home_selection' => false, 'plan' => 'pro', 'plan_started_at' => now()->subMonth(), 'plan_months' => 12]);
    $echu = creatifAnalysable(['in_home_selection' => false, 'plan' => 'pro', 'plan_started_at' => now()->subYears(2), 'plan_months' => 12]);
    $sansAccord = creatifAnalysable(accord: false);

    foreach ([$selectionne, $payant, $echu, $sansAccord] as $c) {
        visuel($c);
    }
    visuel($selectionne)->forceFill(['analysed_at' => now()])->save();

    expect((new LancerAnalyseLot)())->toBe(2);
    Queue::assertPushed(AnalyserMedia::class, 2);

    foreach (range(1, 12) as $i) {
        visuel($selectionne);
    }
    expect((new LancerAnalyseLot)())->toBe(LancerAnalyseLot::TAILLE);
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
