<?php

use App\Models\User;
use App\Services\Paiement\PasserellePayplug;

beforeEach(function () {
    $this->creatif = User::factory()->create(['plan' => 0]);
    $this->payplug = Mockery::mock(PasserellePayplug::class);
    app()->instance(PasserellePayplug::class, $this->payplug);
});

function notification(int $userId, int $option, int $montant, string $id = 'pay_1', bool $paye = true): array
{
    return ['id' => $id, 'paye' => $paye, 'montant' => $montant,
        'metadata' => ['customer_id' => $userId, 'product_id' => 1, 'product_option' => $option], 'brut' => ['id' => $id]];
}

it('propose le 12 mois a un nouveau createur, le reabonnement a un ancien', function () {
    $this->actingAs($this->creatif)->get(route('espace.formule'))->assertSee('36,80')->assertDontSee('29,80');

    $this->creatif->invoices()->create(['number' => 'x', 'brand' => 'ub', 'amount' => 1, 'vat' => 0, 'status' => 'paid', 'issued_at' => now()]);
    $this->actingAs($this->creatif)->get(route('espace.formule'))->assertSee('29,80');
});

it('envoie vers Payplug avec le bon montant', function () {
    $this->payplug->shouldReceive('creerPaiement')->once()
        ->withArgs(fn ($d) => $d['amount'] === 3680 && $d['metadata']['customer_id'] === $this->creatif->id && $d['metadata']['product_option'] === 2)
        ->andReturn(['id' => 'pay_1', 'url' => 'https://secure.payplug.com/pay/x']);

    $this->actingAs($this->creatif)->post(route('espace.formule.payer', 2))->assertRedirect('https://secure.payplug.com/pay/x');
});

it('refuse une option non proposee', function () {
    $this->actingAs($this->creatif)->post(route('espace.formule.payer', 3))->assertNotFound();
});

it('active la formule et facture a la notification, une seule fois', function () {
    $this->payplug->shouldReceive('lireNotification')->twice()->andReturn(notification($this->creatif->id, 2, 3680));

    $this->postJson(route('payplug.notification'), ['id' => 'pay_1'])->assertOk();
    $this->postJson(route('payplug.notification'), ['id' => 'pay_1'])->assertOk();

    $c = $this->creatif->fresh();
    expect($c->plan)->toBe(1)->and($c->plan_months)->toBe(12)
        ->and($c->invoices()->count())->toBe(1)
        ->and($c->invoices()->first())->amount->toBe('36.80')->vat->toBe('6.13')->gateway->toBe('payplug');
});

it('prolonge une formule active a partir de son echeance', function () {
    $this->creatif->update(['plan' => 1, 'plan_started_at' => now()->subMonths(10), 'plan_months' => 12]);
    $this->payplug->shouldReceive('lireNotification')->andReturn(notification($this->creatif->id, 1, 2190));

    $this->postJson(route('payplug.notification'), [])->assertOk();

    expect($this->creatif->fresh()->plan_months)->toBe(18);
});

it('ignore un montant qui ne correspond pas a l option', function () {
    $this->payplug->shouldReceive('lireNotification')->andReturn(notification($this->creatif->id, 4, 100));

    $this->postJson(route('payplug.notification'), [])->assertOk();

    expect($this->creatif->fresh()->plan)->toBe(0)->and($this->creatif->invoices()->count())->toBe(0);
});

/*
 | Le Pack Luxe et le Pack Site se traitent de gre a gre : ils restent
 | dans la grille pour les paiements passes, mais ne se choisissent plus.
 */
it('ne propose plus le Pack Luxe ni le Pack Site', function () {
    $creatif = User::factory()->create();

    $options = app(App\Services\Paiement\Souscription::class)->options($creatif);

    expect(array_keys($options))->not->toContain(4)->not->toContain(5)
        // Il ne reste que le six mois et le douze mois.
        ->and(count($options))->toBe(2);

    $this->actingAs($creatif)->get(route('espace.formule'))
        ->assertOk()
        ->assertSee('Formule gratuite')
        ->assertDontSee('Pack Luxe')
        ->assertDontSee('Pack Site');
});
