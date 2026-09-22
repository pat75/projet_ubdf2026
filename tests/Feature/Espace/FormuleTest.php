<?php

use App\Models\User;

beforeEach(function () {
    $this->creatif = User::factory()->create(['plan' => 1, 'plan_started_at' => now()->subMonths(2), 'plan_months' => 12, 'lastname' => 'Martin']);
    $this->actingAs($this->creatif);
    $this->facture = $this->creatif->invoices()->create([
        'legacy_id' => 7907, 'number' => 'ub-7907', 'brand' => 'ub', 'label' => 'Ultra-book', 'designation' => 'Formule Ultra-book 12 mois',
        'amount' => 29.80, 'vat' => 4.97, 'status' => 'paid', 'issued_at' => '2020-11-02 10:00:00',
    ]);
});

it('affiche la formule, son echeance et les factures payees', function () {
    $this->creatif->invoices()->create(['number' => 'ub-x', 'brand' => 'ub', 'label' => 'Annulee', 'amount' => 1, 'vat' => 0, 'status' => 'cancelled', 'issued_at' => now()]);

    $this->get(route('espace.formule'))->assertOk()
        ->assertSee(now()->subMonths(2)->addMonths(12)->format('d/m/Y'))
        ->assertSee('UB-2020-7907')
        ->assertDontSee('Annulee');
});

it('affiche la facture au format du legacy', function () {
    $this->get(route('espace.facture', $this->facture))->assertOk()
        ->assertSee('Facture n° UB-2020-7907', false)
        ->assertSee('Formule Ultra-book - SaaS 12 mois')
        ->assertSee('24,83 € HT', false)
        ->assertSee('Martin')
        ->assertSee('FR31479054553');
});

it('refuse la facture d un autre creatif', function () {
    $this->actingAs(User::factory()->create())->get(route('espace.facture', $this->facture))->assertForbidden();
});
