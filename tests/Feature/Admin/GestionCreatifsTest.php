<?php

use App\Actions\Admin\PurgerCreatifsNonConfirmes;
use App\Actions\Admin\RenommerCreatif;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Livewire\Espace\Diffusion;
use App\Models\Admin;
use App\Models\User;
use App\Support\DossierBook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']);
});

afterEach(function () {
    foreach (['ancien-nom', 'nouveau-nom', 'jamais-confirme', 'recent-non-confirme', 'confirme-ancien'] as $login) {
        File::deleteDirectory(DossierBook::chemin($login));
    }
});

it('renomme un creatif et deplace son dossier', function () {
    $creatif = User::factory()->create(['login' => 'ancien-nom']);
    File::ensureDirectoryExists(DossierBook::chemin('ancien-nom'));
    File::put(DossierBook::chemin('ancien-nom', 'visuel.jpg'), 'x');

    app(RenommerCreatif::class)($creatif, 'Nouveau-Nom');

    expect($creatif->fresh()->login)->toBe('nouveau-nom')
        ->and(File::exists(DossierBook::chemin('nouveau-nom', 'visuel.jpg')))->toBeTrue()
        ->and(File::exists(DossierBook::chemin('ancien-nom')))->toBeFalse();
});

it('refuse un identifiant pris, reserve ou mal forme', function (string $nouveau) {
    User::factory()->create(['login' => 'deja-pris']);
    $creatif = User::factory()->create(['login' => 'ancien-nom']);

    expect(fn () => app(RenommerCreatif::class)($creatif, $nouveau))->toThrow(ValidationException::class);
    expect($creatif->fresh()->login)->toBe('ancien-nom');
})->with(['deja-pris', 'www', 'avec espace', '']);

it('purge les comptes non confirmes depuis plus de 2 mois, fichiers compris', function () {
    $vieux = User::factory()->create(['login' => 'jamais-confirme', 'email_verified_at' => null]);
    $vieux->forceFill(['created_at' => now()->subMonths(3)])->save();
    File::ensureDirectoryExists(DossierBook::chemin('jamais-confirme'));

    $recent = User::factory()->create(['login' => 'recent-non-confirme', 'email_verified_at' => null]);
    $confirme = User::factory()->create(['login' => 'confirme-ancien']);
    $confirme->forceFill(['created_at' => now()->subYears(2)])->save();

    $purges = app(PurgerCreatifsNonConfirmes::class)(User::all());

    expect($purges)->toBe(1)
        ->and(User::withTrashed()->find($vieux->id))->toBeNull()
        ->and(File::exists(DossierBook::chemin('jamais-confirme')))->toBeFalse()
        ->and($recent->fresh())->not->toBeNull()
        ->and($confirme->fresh())->not->toBeNull();
});

it('note la derniere connexion du createur, pas celle d une prise d identite', function () {
    $creatif = User::factory()->create(['last_login_at' => null]);

    Auth::guard('web')->login($creatif);
    expect($creatif->fresh()->last_login_at)->not->toBeNull();

    $autre = User::factory()->create(['last_login_at' => null]);
    session()->put(\App\Http\Controllers\Admin\PriseIdentiteController::SESSION, $this->admin->id);
    Auth::guard('web')->login($autre);
    expect($autre->fresh()->last_login_at)->toBeNull();
});

it('filtre non confirmes, books d un jour et demandes de selection', function () {
    $this->actingAs($this->admin, 'admin');

    $nonConfirme = User::factory()->create(['email_verified_at' => null]);
    $unJour = User::factory()->create(['last_login_at' => null]);
    $actif = User::factory()->create(['last_login_at' => now()->addDays(3)]);
    $demande = User::factory()->create(['selection_requested_at' => now(), 'last_login_at' => now()->addDay()]);

    Livewire::test(ListUsers::class)
        ->filterTable('non_confirmes')
        ->assertCanSeeTableRecords([$nonConfirme])->assertCanNotSeeTableRecords([$actif, $demande]);

    Livewire::test(ListUsers::class)
        ->filterTable('un_jour')
        ->assertCanSeeTableRecords([$unJour])->assertCanNotSeeTableRecords([$actif, $demande]);

    Livewire::test(ListUsers::class)
        ->filterTable('demande_selection')
        ->assertCanSeeTableRecords([$demande])->assertCanNotSeeTableRecords([$actif, $unJour]);
});

it('laisse le createur demander a etre selectionne', function () {
    $creatif = User::factory()->create();
    $this->actingAs($creatif);

    Livewire::test(Diffusion::class)->assertSee('Demander à être sélectionné')
        ->call('demanderSelection')
        ->assertSee('Demande en cours d’examen')->assertDontSee('Demander à être sélectionné');

    expect($creatif->fresh()->selection_requested_at)->not->toBeNull();

    // Apres 30 jours sans suite, le createur peut refaire une demande.
    $creatif->forceFill(['selection_requested_at' => now()->subDays(29)])->save();
    Livewire::test(Diffusion::class)->assertSee('Demande en cours d’examen');

    $creatif->forceFill(['selection_requested_at' => now()->subDays(31)])->save();
    Livewire::test(Diffusion::class)->assertSee('Demander à être sélectionné')->call('demanderSelection')
        ->assertSee('Demande en cours d’examen');
    expect($creatif->fresh()->selection_requested_at->isToday())->toBeTrue();

    // Mise en selection apres la demande : plus rien a examiner.
    $creatif->forceFill(['in_home_selection' => true, 'home_selection_at' => now()])->saveQuietly();
    Livewire::test(Diffusion::class)->assertSee('fait partie de la sélection')->assertDontSee('Demande en cours');
});

it('filtre par boutons directs : formule payante oui / non, marque', function () {
    $this->actingAs($this->admin, 'admin');

    $payant = User::factory()->create(['plan' => 1, 'brand' => 'ub']);
    $gratuit = User::factory()->create(['plan' => 0, 'brand' => 'df']);

    Livewire::test(ListUsers::class)->filterTable('plan', ['valeur' => 'oui'])
        ->assertCanSeeTableRecords([$payant])->assertCanNotSeeTableRecords([$gratuit]);

    Livewire::test(ListUsers::class)->filterTable('plan', ['valeur' => 'non'])
        ->assertCanSeeTableRecords([$gratuit])->assertCanNotSeeTableRecords([$payant]);

    Livewire::test(ListUsers::class)->filterTable('brand', ['valeur' => 'df'])
        ->assertCanSeeTableRecords([$gratuit])->assertCanNotSeeTableRecords([$payant]);
});

it('filtre les visiteurs par boutons directs', function () {
    $this->actingAs($this->admin, 'admin');

    $df = \App\Models\Visitor::factory()->create(['brand' => 'df']);
    $ub = \App\Models\Visitor::factory()->create(['brand' => 'ub']);

    Livewire::test(\App\Filament\Resources\Visitors\Pages\ListVisitors::class)
        ->filterTable('brand', ['valeur' => 'df'])
        ->assertCanSeeTableRecords([$df])->assertCanNotSeeTableRecords([$ub]);
});

it('bloque et debloque plusieurs comptes d un coup', function () {
    $this->actingAs($this->admin, 'admin');
    [$a, $b] = User::factory()->count(2)->create();
    $v = \App\Models\Visitor::factory()->create();

    Livewire::test(ListUsers::class)->callTableBulkAction('bloquer', [$a, $b], ['motif' => 'spam']);
    expect($a->fresh()->estBloque())->toBeTrue()->and($b->fresh()->blocked_reason)->toBe('spam');

    Livewire::test(ListUsers::class)->callTableBulkAction('debloquer', [$a, $b]);
    expect($a->fresh()->estBloque())->toBeFalse()->and($b->fresh()->estBloque())->toBeFalse();

    Livewire::test(\App\Filament\Resources\Visitors\Pages\ListVisitors::class)->callTableBulkAction('bloquer', [$v]);
    expect($v->fresh()->estBloque())->toBeTrue();
});

it('ouvre le book depuis l identifiant avec la barre de selection', function () {
    $this->actingAs($this->admin, 'admin');
    $creatif = User::factory()->create(['login' => 'revue-test']);
    $url = app(\App\Services\Admin\RevueBooks::class)->url($creatif, $this->admin->id);

    Livewire::test(ListUsers::class)
        ->assertTableColumnStateSet('login', 'revue-test', record: $creatif)
        ->assertSeeHtml(e($url));

    $this->get($url)->assertOk()->assertSee('Pas en sélection');
});
