<?php

use App\Models\MarketingOffer;
use App\Models\User;
use App\Services\Paiement\PasserellePayplug;
use App\Services\Paiement\Souscription;
use Illuminate\Support\Carbon;

afterEach(fn () => Carbon::setTestNow());

function options(User $u): array
{
    return app(Souscription::class)->options($u);
}

it('propose le Black Friday a la place du 12 mois pendant la periode', function () {
    Carbon::setTestNow('2026-11-20 10:00:00');
    $u = User::factory()->create(['created_at' => now()->subWeek()]);

    expect(array_keys(options($u)))->toBe([1, 30, 4, 5])
        ->and(options($u)[30]['ttc'])->toBe(22.00);
});

it('fait primer le Black Friday sur le reabonnement', function () {
    Carbon::setTestNow('2026-11-20 10:00:00');
    $u = User::factory()->create();
    $u->invoices()->create(['number' => 'x', 'brand' => 'ub', 'amount' => 1, 'vat' => 0, 'status' => 'paid', 'issued_at' => now()->subYear()]);

    expect(array_keys(options($u)))->toBe([1, 30, 4, 5]);
});

it('revient aux prix normaux hors periode', function () {
    Carbon::setTestNow('2026-12-15 10:00:00');
    $u = User::factory()->create(['created_at' => now()->subWeek()]);

    expect(array_keys(options($u)))->toBe([1, 2, 4, 5]);
});

it('offre la promo 6 mois 2 mois apres l inscription, 24 h durant, puis tous les 3 mois', function () {
    Carbon::setTestNow('2026-03-01 09:00:00');
    $u = User::factory()->create(['created_at' => '2026-01-15 00:00:00']);
    expect(array_keys(options($u)))->toBe([1, 2, 4, 5]);

    Carbon::setTestNow('2026-03-16 09:00:00');
    expect(array_keys(options($u)))->toBe([10, 2, 4, 5])
        ->and(MarketingOffer::where('user_id', $u->id)->count())->toBe(1);

    Carbon::setTestNow('2026-03-17 08:00:00');
    expect(array_keys(options($u)))->toContain(10);

    Carbon::setTestNow('2026-03-18 09:00:00');
    expect(array_keys(options($u)))->not->toContain(10);

    Carbon::setTestNow('2026-06-17 09:00:00');
    expect(array_keys(options($u)))->toContain(10)
        ->and(MarketingOffer::where('user_id', $u->id)->count())->toBe(2);
});

it('ne propose pas la promo 6 mois a qui a deja paye', function () {
    Carbon::setTestNow('2026-06-17 09:00:00');
    $u = User::factory()->create(['created_at' => '2025-01-01']);
    $u->invoices()->create(['number' => 'x', 'brand' => 'ub', 'amount' => 1, 'vat' => 0, 'status' => 'paid', 'issued_at' => '2025-02-01']);

    expect(array_keys(options($u)))->toBe([1, 3, 4, 5]);
});

it('encaisse la promo au prix promotionnel', function () {
    Carbon::setTestNow('2026-11-20 10:00:00');
    $u = User::factory()->create();
    $payplug = Mockery::mock(PasserellePayplug::class);
    app()->instance(PasserellePayplug::class, $payplug);
    $payplug->shouldReceive('creerPaiement')->once()->withArgs(fn ($d) => $d['amount'] === 2200)->andReturn(['id' => 'p', 'url' => 'https://pay']);
    $payplug->shouldReceive('lireNotification')->andReturn(['id' => 'p', 'paye' => true, 'montant' => 2200,
        'metadata' => ['customer_id' => $u->id, 'product_option' => 30], 'brut' => ['id' => 'p']]);

    $this->actingAs($u)->post(route('espace.formule.payer', 30))->assertRedirect('https://pay');
    $this->postJson(route('payplug.notification'), [])->assertOk();

    expect($u->fresh()->plan_months)->toBe(12)->and($u->invoices()->sole()->amount)->toBe('22.00');
});
