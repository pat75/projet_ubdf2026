<?php

use App\Models\User;
use App\Services\Stats\VisitesAjoutees;

it('ne depasse jamais 4 visites par jour ni le plafond', function () {
    $service = new VisitesAjoutees;

    foreach ([0, 500, 990, 998] as $vues) {
        for ($i = 0; $i < 50; $i++) {
            $plan = $service->planifier($vues, 60, now());
            expect(max($plan ?: [0]))->toBeLessThanOrEqual(VisitesAjoutees::MAX_PAR_JOUR)
                ->and($vues + array_sum($plan))->toBeLessThan(VisitesAjoutees::PLAFOND);
        }
    }
});

it('affiche la page Statistiques avec les books du lot', function () {
    $admin = App\Models\Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']);
    $book = User::factory()->create(['login' => 'ariane']);
    $book->visitesAjoutees()->create(['lot' => '01K0000000000000000000000', 'prevue_at' => now()->addDay(), 'nb' => 2]);

    $this->actingAs($admin, 'admin')->get('/admin_/statistiques')
        ->assertOk()->assertSee('ariane')->assertSee('Ajouter des visites à 10 portfolios');
});

it('verse les visites echues puis les retire', function () {
    $book = User::factory()->create();
    $book->visitesAjoutees()->create(['lot' => '01K0000000000000000000000', 'prevue_at' => now()->subHour(), 'nb' => 3]);
    $book->visitesAjoutees()->create(['lot' => '01K0000000000000000000000', 'prevue_at' => now()->addDay(), 'nb' => 2]);

    $service = new VisitesAjoutees;
    expect($service->appliquer())->toBe(1)
        ->and((int) $book->visitStats()->sum('public_views'))->toBe(3);

    $service->retirer($book);
    expect((int) $book->visitStats()->sum('public_views'))->toBe(0)
        ->and($book->visitesAjoutees()->count())->toBe(0);
});
