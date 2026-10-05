<?php

use App\Models\Admin;
use App\Models\User;

beforeEach(function () {
    $this->creatif = User::factory()->create(['lastname' => 'Martin', 'company' => 'Atelier Martin']);
    $this->facture = $this->creatif->invoices()->create([
        'legacy_id' => 7907, 'number' => 'ub-7907', 'brand' => 'ub', 'label' => 'Ultra-book',
        'designation' => 'Formule Ultra-book 12 mois', 'amount' => 36.80, 'vat' => 6.13,
        'status' => 'paid', 'issued_at' => '2026-02-10 09:00:00',
    ]);
});

it('telecharge la facture en PDF', function () {
    $reponse = $this->actingAs($this->creatif)->get(route('espace.facture.pdf', $this->facture))->assertOk();

    expect($reponse->headers->get('content-type'))->toContain('application/pdf')
        ->and($reponse->headers->get('content-disposition'))->toContain('UB-2026-7907.pdf')
        ->and($reponse->getContent())->toStartWith('%PDF');
});

it('propose le PDF depuis la facture affichee', function () {
    $this->actingAs($this->creatif)->get(route('espace.facture', $this->facture))
        ->assertOk()->assertSee('Télécharger en PDF');
});

it('refuse le PDF de la facture d un autre creatif', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('espace.facture.pdf', $this->facture))->assertForbidden();

    auth()->logout();
    $this->get(route('espace.facture.pdf', $this->facture))->assertRedirect();
});

it('donne la facture et son PDF a un administrateur', function () {
    $admin = Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']);

    $this->actingAs($admin, 'admin');
    $this->get('/admin_/factures/'.$this->facture->id)->assertOk()->assertSee('Atelier Martin');
    $this->get(route('admin.facture.pdf', $this->facture))->assertOk();
});
