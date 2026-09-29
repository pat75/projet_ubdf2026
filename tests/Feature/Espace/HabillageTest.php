<?php

use App\Livewire\Espace\Diffusion;
use App\Livewire\Espace\Habillage;
use App\Models\User;
use App\Services\Espace\ReglagesTheme;
use App\Support\DossierBook;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

beforeEach(function () {
    $this->creatif = User::factory()->create();
    $this->actingAs($this->creatif);
});

it('place la presentation en tete et ne propose plus les reglages du modele', function () {
    $this->creatif->bookSetting()->create(['theme' => 'mdl_2016_zoom']);

    $this->get(route('espace.design'))->assertOk()
        ->assertSeeInOrder(['Profil', 'Modèle du book', 'Modèle Zoom 2016'])
        ->assertDontSee('Réglages du modèle')
        ->assertDontSee('Link bio');
});

it('enregistre les reglages en gardant le format du legacy', function () {
    $this->creatif->bookSetting()->create(['theme' => 'mdl_2016_zoom']);

    $composant = Livewire::test(Habillage::class);
    $valeurs = $composant->get('valeurs');
    $champs = app(ReglagesTheme::class)->champs(json_decode(config('book_themes.mdl_2016_zoom.defaut'), true));
    $i = collect($champs)->search(fn ($c) => $c['chemin'] === ".ub_couleur_fond\x1FbackgroundColor");
    $j = collect($champs)->search(fn ($c) => $c['chemin'] === "ptf_activer_gmap\x1Fptf_activer_gmap");
    $valeurs[$i] = '#123456';
    $valeurs[$j] = true;

    $composant->set('valeurs', $valeurs)->call('enregistrer');

    $r = $this->creatif->bookSetting->fresh();
    expect($r->theme_settings['data']['.ub_couleur_fond']['backgroundColor'])->toBe('#123456')
        ->and($r->theme_settings['data']['ptf_activer_gmap']['ptf_activer_gmap'])->toBe('true');
});

it('rejette une couleur invalide', function () {
    $this->creatif->bookSetting()->create(['theme' => 'mdl_2016_zoom']);
    $champs = app(ReglagesTheme::class)->champs(json_decode(config('book_themes.mdl_2016_zoom.defaut'), true));
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

afterEach(function () {
    File::deleteDirectory(DossierBook::chemin($this->creatif->login ?? '_'));
});

it('depose une photo de profil recadree et l affiche a la place des initiales', function () {
    $fichier = UploadedFile::fake()->image('avatar.jpg', 400, 400);

    Livewire::test(Habillage::class)
        ->set('avatarTemp', $fichier)
        ->call('deposerAvatar')
        ->assertHasNoErrors()
        // Declinaison carree : le rond affiche montre le recadrage choisi.
        ->assertDispatched('avatar-profil-modifie', fn ($nom, $params) => str_contains($params['url'], '/carre_368/'));

    $reglages = $this->creatif->bookSetting()->first();
    expect($reglages->thumbnail)->not->toBeNull()
        ->and(is_file(DossierBook::chemin($this->creatif->login, $reglages->thumbnail)))->toBeTrue();
});

it('retire la photo de profil et revient aux initiales', function () {
    $fichier = UploadedFile::fake()->image('avatar.jpg', 400, 400);
    $composant = Livewire::test(Habillage::class)->set('avatarTemp', $fichier)->call('deposerAvatar');
    $ancien = $this->creatif->bookSetting()->first()->thumbnail;

    $composant->call('retirerAvatar')
        // Les autres vignettes de la page repassent aux initiales.
        ->assertDispatched('avatar-profil-modifie', url: null);

    expect($this->creatif->bookSetting()->first()->thumbnail)->toBeNull()
        ->and(is_file(DossierBook::chemin($this->creatif->login, $ancien)))->toBeFalse();
});

it('enregistre un champ de la presentation seul, a la sortie du champ', function () {
    Livewire::test(Habillage::class)
        ->call('enregistrerChamp', 'titre', 'Mon book')
        ->assertReturned(['ok' => true])
        ->call('enregistrerChamp', 'piedDePage', '© Adolie')
        ->assertReturned(['ok' => true]);

    $r = $this->creatif->bookSetting()->first();
    expect($r->title)->toBe('Mon book')->and($r->footer)->toBe('© Adolie');
});

it('renvoie l erreur de validation sans rien enregistrer', function () {
    $this->creatif->bookSetting()->create(['theme' => 'mdl_2016_zoom', 'title' => 'Avant']);

    $reponse = Livewire::test(Habillage::class)->call('enregistrerChamp', 'titre', str_repeat('x', 300))->effects['returns'][0];

    expect($reponse)->toHaveKey('erreur')
        ->and($this->creatif->bookSetting()->first()->title)->toBe('Avant');
});

it('refuse d enregistrer un champ non declare', function () {
    Livewire::test(Habillage::class)->call('enregistrerChamp', 'theme', 'pirate')->assertStatus(422);
});

it('enregistre la diffusion', function () {
    Livewire::test(Diffusion::class)->set('web', true)->set('disponible', false)
        ->call('basculer', 'web')->call('basculer', 'disponible')
        ->assertSee('2 / 3 canaux actifs');

    expect($this->creatif->bookSetting()->first())
        ->diffuse_web->toBeFalse()->diffuse_availability->toBeTrue();
});

it('refuse de basculer un champ non declare', function () {
    Livewire::test(Diffusion::class)->call('basculer', 'login')->assertStatus(422);
});

it('ne propose pas les anciens modeles a un book cree apres 2015', function () {
    $this->creatif->forceFill(['created_at' => '2018-03-01'])->save();
    $this->creatif->bookSetting()->create(['theme' => 'mdl_2020_ultra_frais']);

    $this->get(route('espace.design'))->assertOk()->assertDontSee('Anciens modèles');
});

it('propose les anciens modeles a un book cree jusqu en 2015', function () {
    $this->creatif->forceFill(['created_at' => '2012-05-10'])->save();
    $this->creatif->bookSetting()->create(['theme' => 'mdl_2020_ultra_frais']);

    $this->get(route('espace.design'))->assertOk()->assertSee('Anciens modèles');
});

it('garde les anciens modeles visibles pour un book recent qui en porte un', function () {
    $this->creatif->forceFill(['created_at' => '2018-03-01'])->save();
    $this->creatif->bookSetting()->create(['theme' => 'mdl_2014_responsive']);

    $this->get(route('espace.design'))->assertOk()->assertSee('Anciens modèles');
});
