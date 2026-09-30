<?php

use App\Filament\Pages\AccueilPage;
use App\Models\Admin;
use App\Models\Reglage;
use App\Models\User;
use App\Models\Visitor;
use Livewire\Livewire;

beforeEach(function () {
    config(['marques.marques.ub.hotes' => ['ubdf2026.ultra-book.name']]);
    $this->portail = 'https://ubdf2026.ultra-book.name';
});

it('sert le portail quand la maintenance est fermee', function () {
    $this->get($this->portail.'/')->assertOk();
});

it('ferme tout le portail avec la page de maintenance', function () {
    Reglage::definir(Reglage::MAINTENANCE, true);

    foreach (['/', '/accueil', '/recherche', '/creer-un-book'] as $chemin) {
        $this->get($this->portail.$chemin)
            ->assertStatus(503)
            ->assertSee('Site en maintenance')
            ->assertSee('/img_front/recherche-vide.png', false)
            ->assertSee('<strong>'.now()->translatedFormat('l').'</strong>', false)
            ->assertSee('<strong>quelques minutes à quelques heures</strong>', false);
    }
});

it('refuse la connexion et la creation de compte', function () {
    Reglage::definir(Reglage::MAINTENANCE, true);

    $this->post($this->portail.'/ubaction__user_open', ['login' => 'ariane', 'password' => 'x'])
        ->assertStatus(503);
    $this->post($this->portail.'/inscription', [])->assertStatus(503);
    $this->get($this->portail.'/auth/google')->assertStatus(503);
    $this->post($this->portail.'/memo/compte', [])->assertStatus(503);
});

it('rend du JSON aux appels ajax', function () {
    Reglage::definir(Reglage::MAINTENANCE, true);

    $this->postJson($this->portail.'/inscription', [])
        ->assertStatus(503)
        ->assertJsonStructure(['message']);
});

it('deconnecte un creatif et lui ferme l espace', function () {
    Reglage::definir(Reglage::MAINTENANCE, true);

    $this->actingAs(User::factory()->create())
        ->get($this->portail.'/espace')
        ->assertStatus(503);

    expect(auth('web')->check())->toBeFalse();
});

it('deconnecte un visiteur', function () {
    Reglage::definir(Reglage::MAINTENANCE, true);

    $this->actingAs(Visitor::factory()->create(), 'visitor')
        ->get($this->portail.'/')
        ->assertStatus(503);

    expect(auth('visitor')->check())->toBeFalse();
});

it('laisse les books creatifs en ligne', function () {
    $creatif = User::factory()->create(['login' => 'aurelie-b']);
    Reglage::definir(Reglage::MAINTENANCE, true);

    $this->get('https://'.$creatif->login.'.'.config('ubdf.book_domain').'/')->assertOk();
});

it('laisse le back-office accessible et affiche le badge', function () {
    $admin = Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']);

    $this->actingAs($admin, 'admin');
    $this->get($this->portail.'/admin/accueil-page')->assertOk()->assertDontSee('ub-badge-maintenance', false);

    Reglage::definir(Reglage::MAINTENANCE, true);

    $this->get($this->portail.'/admin/accueil-page')->assertOk()->assertSee('ub-badge-maintenance', false);
});

it('enregistre la maintenance depuis la page d administration', function () {
    $admin = Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']);

    Livewire::actingAs($admin, 'admin')
        ->test(AccueilPage::class)
        ->assertSet('data.'.Reglage::MAINTENANCE, false)
        ->set('data.'.Reglage::MAINTENANCE, true)
        ->call('save');

    expect(Reglage::enMaintenance())->toBeTrue();
});

it('ferme aussi le portail pour un administrateur connecte', function () {
    $admin = Admin::create(['name' => 'Pat', 'email' => 'chef@example.test', 'password' => 'mot-de-passe-long']);
    Reglage::definir(Reglage::MAINTENANCE, true);

    $this->actingAs($admin, 'admin')
        ->get($this->portail.'/')
        ->assertStatus(503)
        ->assertSee('Site en maintenance');
});

it('rouvre le site quand l administrateur decoche la maintenance', function () {
    $admin = Admin::create(['name' => 'Pat', 'email' => 'rouvre@example.test', 'password' => 'mot-de-passe-long']);
    Reglage::definir(Reglage::MAINTENANCE, true);

    Livewire::actingAs($admin, 'admin')
        ->test(AccueilPage::class)
        ->assertSet('data.'.Reglage::MAINTENANCE, true)
        ->set('data.'.Reglage::MAINTENANCE, false)
        ->call('save')
        ->assertHasNoErrors();

    expect(Reglage::enMaintenance())->toBeFalse();
    $this->get($this->portail.'/')->assertOk();
});

/*
 | Le test Livewire ci-dessus passe a cote des middlewares HTTP : c'est
 | par la requete reelle `livewire-<hash>/update` que le bouton
 | « Enregistrer » etait bloque en 503. Le contenu n'a pas a etre valide
 | (la somme de controle echoue apres le middleware) : seul compte que la
 | maintenance ne reponde pas a sa place.
 */
function appelLivewire(string $composant): array
{
    return ['components' => [[
        'snapshot' => json_encode(['data' => [], 'memo' => ['name' => $composant, 'id' => 'x'], 'checksum' => 'x']),
        'updates' => [],
        'calls' => [],
    ]]];
}

it('laisse passer les appels Livewire du back-office, pas ceux de l espace', function () {
    Reglage::definir(Reglage::MAINTENANCE, true);
    $url = $this->portail.app('livewire')->getUpdateUri();

    $admin = $this->withHeader('X-Livewire', '1')->postJson($url, appelLivewire(AccueilPage::class));
    expect($admin->status())->not->toBe(503);

    $espace = $this->withHeader('X-Livewire', '1')->postJson($url, appelLivewire('App\\Livewire\\Espace\\Messages'));
    expect($espace->status())->toBe(503);
});
