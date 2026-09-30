<?php

use App\Filament\Widgets\ChiffreAffairesAnnuel;
use App\Filament\Widgets\ChiffresCles;
use App\Filament\Widgets\DernieresInscriptionsCreatifs;
use App\Filament\Widgets\DernieresInscriptionsVisiteurs;
use App\Filament\Widgets\Desinscriptions;
use App\Filament\Widgets\Encaissements;
use App\Filament\Widgets\InscriptionsCreatifs;
use App\Filament\Widgets\InscriptionsVisiteurs;
use App\Models\Admin;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Visitor;
use Filament\Facades\Filament;
use Livewire\Livewire;

/** Les donnees Chart.js du graphique, que le widget garde protegees. */
function donnees(object $widget): array
{
    return (fn () => $this->getCachedData())->call($widget);
}

beforeEach(function () {
    $this->admin = Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']);
    $this->actingAs($this->admin, 'admin');
});

it('pose les quatre courbes et les deux listes sur l’accueil', function () {
    $this->get('/admin')->assertOk();

    expect(Filament::getWidgets())->toContain(
        ChiffreAffairesAnnuel::class,
        InscriptionsCreatifs::class,
        InscriptionsVisiteurs::class,
        Desinscriptions::class,
        DernieresInscriptionsCreatifs::class,
        DernieresInscriptionsVisiteurs::class,
        Encaissements::class,
    );
});

it('chiffre l’encaissé du jour, de la semaine, du mois et de l’annee', function () {
    $creatif = User::factory()->create();
    foreach ([now(), now()->subDays(3), now()->subMonth(), now()->subYear()] as $date) {
        Invoice::create(['user_id' => $creatif->id, 'brand' => 'ub', 'number' => uniqid(),
            'designation' => 'Formule', 'amount' => 100, 'vat' => 0,
            'status' => 'paid', 'issued_at' => $date]);
    }

    $libelles = collect(Livewire::test(Encaissements::class)->instance()->lignes())
        ->mapWithKeys(fn (array $ligne) => [$ligne['libelle'] => $ligne['montant']]);

    expect($libelles['Aujourd’hui'])->toBe('100,00 €')
        ->and($libelles['7 derniers jours'])->toBe('200,00 €')
        // Le mois dernier tombe hors du mois en cours, mais dans l'annee.
        ->and($libelles['Depuis le 1er janvier'])->toBe('300,00 €');
});

it('affiche chaque bloc du tableau de bord sans erreur', function () {
    // Les blocs se chargent en differe : la page d'accueil ne les rend
    // pas elle-meme, seul un rendu Livewire les traverse pour de bon.
    foreach ([ChiffresCles::class, ChiffreAffairesAnnuel::class, Encaissements::class,
        InscriptionsCreatifs::class, InscriptionsVisiteurs::class, Desinscriptions::class,
        DernieresInscriptionsCreatifs::class, DernieresInscriptionsVisiteurs::class] as $bloc) {
        Livewire::test($bloc)->assertSuccessful();
    }
});

it('trace deux annees, l’annee en cours et la precedente', function () {
    $donnees = donnees(Livewire::test(ChiffreAffairesAnnuel::class)->instance());

    expect($donnees['datasets'])->toHaveCount(2)
        ->and($donnees['datasets'][0]['label'])->toBe((string) now()->year)
        ->and($donnees['datasets'][1]['label'])->toBe((string) now()->subYear()->year)
        ->and($donnees['labels'])->toHaveCount(53);
});

it('compte creatifs et visiteurs dans la courbe des desinscriptions', function () {
    User::factory()->create()->delete();
    Visitor::factory()->create()->delete();

    $serie = donnees(Livewire::test(Desinscriptions::class)->instance())['datasets'][0]['data'];

    expect(max(array_filter($serie, fn ($v) => $v !== null)))->toBe(2.0);
});

it('ne montre que les dix derniers inscrits, les plus recents en tete', function () {
    User::factory()->count(12)->create(['created_at' => now()->subYear()]);
    $dernier = User::factory()->create(['login' => 'tout-frais', 'created_at' => now()]);

    $liste = Livewire::test(DernieresInscriptionsCreatifs::class)
        ->assertCanSeeTableRecords([$dernier]);

    expect($liste->instance()->getTableRecords())->toHaveCount(10);
});

it('offre la selection et la prise d’identite sur un creatif, la seule prise d’identite sur un visiteur', function () {
    $creatif = User::factory()->create();
    $visiteur = Visitor::factory()->create();

    Livewire::test(DernieresInscriptionsCreatifs::class)
        ->assertTableActionExists('selection')
        ->assertTableActionExists('prise_identite')
        ->callTableAction('selection', $creatif);

    expect($creatif->refresh()->in_home_selection)->toBeTrue();

    Livewire::test(DernieresInscriptionsVisiteurs::class)
        ->assertTableActionExists('prise_identite')
        ->assertTableActionDoesNotExist('selection');
});
