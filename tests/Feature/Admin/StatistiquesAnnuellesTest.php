<?php

use App\Models\Invoice;
use App\Models\User;
use App\Models\Visitor;
use App\Services\Admin\StatistiquesAnnuelles;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Mi-mars : l'annee en cours est entamee, mais loin d'etre finie —
    // c'est la situation ou la coupe « a la meme date » se voit.
    $this->travelTo('2026-03-10 12:00:00');
    $this->stats = app(StatistiquesAnnuelles::class);
});

function comptes(): array
{
    return [[User::query(), 'created_at', null]];
}

it('cumule semaine par semaine sur les 53 semaines de l’annee', function () {
    User::factory()->create(['created_at' => '2025-01-02']);   // semaine 1
    User::factory()->create(['created_at' => '2025-01-09']);   // semaine 2
    User::factory()->create(['created_at' => '2025-12-31']);   // semaine 53

    $serie = $this->stats->cumul(comptes(), 2025);

    expect($serie)->toHaveCount(53)
        ->and($serie[0])->toBe(1.0)
        ->and($serie[1])->toBe(2.0)
        ->and($serie[51])->toBe(2.0)
        ->and($serie[52])->toBe(3.0);
});

it('arrete la courbe de l’annee en cours a la semaine du jour', function () {
    User::factory()->create(['created_at' => '2026-01-05']);

    $serie = $this->stats->cumul(comptes(), 2026);

    // 10 mars = 69e jour, soit la 10e semaine : dix valeurs, puis rien.
    expect($serie[9])->toBe(1.0)
        ->and($serie[10])->toBeNull()
        ->and($serie[52])->toBeNull();
});

it('somme une colonne plutot que de compter les lignes', function () {
    $creatif = User::factory()->create();
    foreach ([['2025-01-02', 36.80], ['2025-01-03', 29.80]] as [$date, $montant]) {
        Invoice::create(['user_id' => $creatif->id, 'brand' => 'ub', 'number' => uniqid(),
            'designation' => 'Formule', 'amount' => $montant, 'vat' => 0,
            'status' => 'paid', 'issued_at' => $date]);
    }

    $serie = $this->stats->cumul([[Invoice::query()->where('status', 'paid'), 'issued_at', 'amount']], 2025);

    expect($serie[0])->toBe(66.60)->and($serie[52])->toBe(66.60);
});

it('ignore les lignes des autres annees', function () {
    User::factory()->create(['created_at' => '2024-06-01']);
    User::factory()->create(['created_at' => '2025-06-01']);

    $serie = $this->stats->cumul(comptes(), 2025);

    expect(end($serie))->toBe(1.0);
});

it('additionne plusieurs sources dans une seule courbe', function () {
    User::factory()->create()->delete();
    Visitor::factory()->create()->delete();

    $serie = $this->stats->cumul([
        [User::onlyTrashed(), 'deleted_at', null],
        [Visitor::onlyTrashed(), 'deleted_at', null],
    ], 2026);

    expect($serie[9])->toBe(2.0);
});

it('compare les deux annees a la meme date', function () {
    User::factory()->create(['created_at' => '2025-01-15']);  // avant le 10 mars
    User::factory()->create(['created_at' => '2025-08-15']);  // apres : hors comparaison
    User::factory()->create(['created_at' => '2026-01-15']);
    User::factory()->create(['created_at' => '2026-02-15']);

    $comparaison = $this->stats->comparaison(comptes());

    expect($comparaison['total'])->toBe(2.0)
        ->and($comparaison['total_precedent'])->toBe(1.0)
        ->and($comparaison['ecart'])->toBe(100.0);
});

it('ne divise pas par zero quand l’annee precedente est vide', function () {
    User::factory()->create(['created_at' => '2026-01-15']);

    expect($this->stats->comparaison(comptes())['ecart'])->toBeNull();
});

it('garde la serie en cache dix minutes', function () {
    Cache::flush();

    $this->stats->cumul(comptes(), 2025, cle: 'creatifs');
    User::factory()->create(['created_at' => '2025-02-02']);

    expect($this->stats->cumul(comptes(), 2025, cle: 'creatifs')[52])->toBe(0.0);
});
