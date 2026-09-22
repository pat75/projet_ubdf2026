<?php

use App\Livewire\Espace\Diffusion;
use App\Livewire\Espace\Habillage;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->creatif = User::factory()->create();
    $this->actingAs($this->creatif);
});

it('affiche les reglages du theme actif', function () {
    $this->creatif->bookSetting()->create(['theme' => 'mdl_2016_zoom']);

    $this->get(route('espace.design'))->assertOk()->assertSee('Modèle Zoom 2016')->assertSee('Link bio');
});

it('enregistre les reglages en gardant le format du legacy', function () {
    $this->creatif->bookSetting()->create(['theme' => 'mdl_2016_zoom']);

    $composant = Livewire::test(Habillage::class);
    $valeurs = $composant->get('valeurs');
    $champs = app(App\Services\Espace\ReglagesTheme::class)->champs(json_decode(config('book_themes.mdl_2016_zoom.defaut'), true));
    $i = collect($champs)->search(fn ($c) => $c['chemin'] === ".ub_couleur_fond\x1FbackgroundColor");
    $j = collect($champs)->search(fn ($c) => $c['chemin'] === "ptf_activer_gmap\x1Fptf_activer_gmap");
    $valeurs[$i] = '#123456';
    $valeurs[$j] = true;

    $composant->set('valeurs', $valeurs)->set('titre', 'Mon book')->call('enregistrer');

    $r = $this->creatif->bookSetting->fresh();
    expect($r->title)->toBe('Mon book')
        ->and($r->theme_settings['data']['.ub_couleur_fond']['backgroundColor'])->toBe('#123456')
        ->and($r->theme_settings['data']['ptf_activer_gmap']['ptf_activer_gmap'])->toBe('true');
});

it('rejette une couleur invalide', function () {
    $this->creatif->bookSetting()->create(['theme' => 'mdl_2016_zoom']);
    $champs = app(App\Services\Espace\ReglagesTheme::class)->champs(json_decode(config('book_themes.mdl_2016_zoom.defaut'), true));
    $i = collect($champs)->search(fn ($c) => $c['type'] === 'couleur');

    $composant = Livewire::test(Habillage::class);
    $valeurs = $composant->get('valeurs');
    $valeurs[$i] = 'red;}</style><script>';
    $composant->set('valeurs', $valeurs)->call('enregistrer');

    expect(json_encode($this->creatif->bookSetting->fresh()->theme_settings))->not->toContain('script');
});

it('conserve les reglages de chaque theme en changeant de modele', function () {
    $this->creatif->bookSetting()->create([
        'theme' => 'mdl_2016_zoom',
        'theme_settings' => ['data' => ['.ub_couleur_fond' => ['backgroundColor' => '#abcdef']]],
    ]);

    Livewire::test(Habillage::class)->call('choisirTheme', 'mdl_2015_grid');
    expect($this->creatif->bookSetting->fresh()->theme)->toBe('mdl_2015_grid');

    Livewire::test(Habillage::class)->call('choisirTheme', 'mdl_2016_zoom');
    expect($this->creatif->bookSetting->fresh()->theme_settings['data']['.ub_couleur_fond']['backgroundColor'])->toBe('#abcdef');
});

it('refuse un theme inconnu', function () {
    Livewire::test(Habillage::class)->call('choisirTheme', 'pirate')->assertStatus(422);
});

it('enregistre la diffusion', function () {
    Livewire::test(Diffusion::class)->set('web', false)->set('disponible', true)->call('enregistrer');

    expect($this->creatif->bookSetting()->first())
        ->diffuse_web->toBeFalse()->diffuse_availability->toBeTrue();
});
