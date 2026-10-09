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

it('extrait le JSON d une reponse bavarde', function () {
    $r = AnalyseImage::lireReponse('Voici le resultat : {"titre": "Renard", "tags_fr": ["renard", "neige", "hiver"]} Bonne journee.');

    expect($r['titre'])->toBe('Renard')->and($r['tags_fr'])->toHaveCount(3);
});

it('refuse une reponse illisible ou trop pauvre', function (string $contenu) {
    AnalyseImage::lireReponse($contenu);
})->throws(RuntimeException::class)->with([
    'pas du json' => 'Voici un renard.',
    'deux mots-cles' => json_encode(['titre' => 'x', 'tags_fr' => ['a', 'b']]),
    // Titres vus en production, identiques d'une image a l'autre.
    'titre generique' => json_encode(['titre' => 'Portfolio de créatif', 'tags_fr' => ['a', 'b', 'c']]),
    'titre generique 2' => json_encode(['titre' => 'Portfolio de créativité visuelle', 'tags_fr' => ['a', 'b', 'c']]),
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

it('montre au createur ses mots-cles et lui laisse retirer les siens seulement', function () {
    $creatif = creatifAnalysable();
    $autre = creatifAnalysable();
    $renard = App\Models\Tag::create(['label' => 'renard', 'lang' => 'fr']);
    $hiver = App\Models\Tag::create(['label' => 'hiver', 'lang' => 'fr']);
    visuel($creatif)->tags()->attach([$renard->id, $hiver->id]);
    visuel($autre)->tags()->attach($renard->id);

    Livewire\Livewire::actingAs($creatif)->test(App\Livewire\Espace\Diffusion::class)
        ->assertSee('Voir les 2 mots-clés trouvés dans mes visuels')
        ->assertSee('renard')->assertSee('hiver')
        ->call('supprimerMotCle', $renard->id)
        ->assertSee('Voir le mot-clé trouvé dans mes visuels');

    expect($renard->media()->where('media.user_id', $creatif->id)->count())->toBe(0)
        ->and($renard->media()->where('media.user_id', $autre->id)->count())->toBe(1);
});

it('passe au modele suivant quand une reponse est hors format, consigne dans le message utilisateur', function () {
    config(['services.nvidia.api_key' => 'cle-test']);
    $fichier = tempnam(sys_get_temp_dir(), 'img');
    file_put_contents($fichier, 'jpeg');
    $this->mock(GenerateurImages::class)->shouldReceive('produire')->andReturn($fichier);

    Http::fakeSequence('integrate.api.nvidia.com/*')
        ->push(['choices' => [['message' => ['content' => 'Here is the rewritten caption: a fox.']]]])
        ->push(['choices' => [['message' => ['content' => [['type' => 'text', 'text' => json_encode([
            'titre' => 'Renard', 'tags_fr' => ['renard', 'hiver', 'aquarelle'], 'tags_en' => ['fox'],
        ])]]]]]]);

    $media = visuel(creatifAnalysable());
    app(AnalyseImage::class)->analyser($media);

    expect($media->fresh()->ai_status)->toBe('ok');
    Http::assertSent(fn ($requete) => $requete['messages'][0]['role'] === 'user'
        && $requete['messages'][0]['content'][0]['type'] === 'text');
});

it('bascule sur OpenRouter quand NVIDIA ne repond plus, sans le retenter pendant la pause', function () {
    config(['services.nvidia.api_key' => 'cle-test', 'services.openrouter.api_key' => 'test-key']);
    \Illuminate\Support\Facades\Cache::flush();
    $fichier = tempnam(sys_get_temp_dir(), 'img');
    file_put_contents($fichier, 'jpeg');
    $this->mock(GenerateurImages::class)->shouldReceive('produire')->andReturn($fichier);
    $reponse = fn (string $titre) => ['choices' => [['message' => ['content' => json_encode([
        'titre' => $titre, 'tags_fr' => ['renard', 'hiver', 'aquarelle'], 'tags_en' => ['fox'],
    ])]]]];
    Http::fake([
        'integrate.api.nvidia.com/*' => Http::response('saturé', 503),
        'openrouter.ai/*' => Http::sequence()->push($reponse('Renard'))->push($reponse('Chat')),
    ]);
    $nvidia = fn () => Http::recorded(fn ($r) => str_contains($r->url(), 'nvidia'))->count();

    $media = visuel(creatifAnalysable());
    app(AnalyseImage::class)->analyser($media);
    $appels = $nvidia();

    $autre = visuel(creatifAnalysable());
    app(AnalyseImage::class)->analyser($autre);

    expect($media->fresh()->ai_title)->toBe('Renard')
        ->and($autre->fresh()->ai_title)->toBe('Chat')
        ->and($appels)->toBeGreaterThan(0)
        ->and($nvidia())->toBe($appels); // pause : NVIDIA pas rappele
});
