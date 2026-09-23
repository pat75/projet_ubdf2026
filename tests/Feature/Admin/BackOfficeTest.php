<?php

use App\Models\Admin;
use App\Models\Conversation;
use App\Models\PromoCode;
use App\Models\User;

beforeEach(function () {
    $this->admin = Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']);
});

it('demande une authentification pour entrer', function () {
    $this->get('/admin')->assertRedirect();
    $this->get('/admin/login')->assertOk();
});

it('refuse un compte createur', function () {
    $this->actingAs(User::factory()->create())->get('/admin')->assertRedirect();
});

it('refuse un administrateur desactive', function () {
    $this->admin->update(['is_active' => false]);

    $this->actingAs($this->admin, 'admin')->get('/admin')->assertForbidden();
});

it('liste les creatifs, les factures et les demandes', function () {
    $creatif = User::factory()->create(['login' => 'ariane']);
    $creatif->invoices()->create(['number' => 'ub-1', 'brand' => 'ub', 'designation' => 'Formule 12 mois',
        'amount' => 36.80, 'vat' => 6.13, 'status' => 'paid', 'issued_at' => now()]);
    Conversation::create(['user_id' => $creatif->id, 'channel' => 'book', 'sender_name' => 'Client Dupont', 'selector' => str_repeat('a', 24)]);

    $this->actingAs($this->admin, 'admin');
    $this->get('/admin/users')->assertOk()->assertSee('ariane');
    $this->get('/admin/invoices')->assertOk();
    $this->get('/admin/conversations')->assertOk()->assertSee('Client Dupont');
});

it('permet de creer un code promo utilisable par un creatif', function () {
    $this->actingAs($this->admin, 'admin')->get('/admin/promo-codes/create')->assertOk();

    PromoCode::create(['code' => 'ADMIN2026', 'discount' => 3, 'discount_type' => PromoCode::MOIS, 'max_uses' => 1]);
    $creatif = User::factory()->create(['plan' => 0]);

    expect(app(App\Services\Paiement\Parrainage::class)->utiliserCode($creatif, 'ADMIN2026'))->toContain('3 mois')
        ->and($creatif->fresh()->plan_months)->toBe(3);
});

it('ouvre chaque ecran du back-office', function () {
    $this->actingAs($this->admin, 'admin');

    $this->get('/admin')->assertOk()->assertSee('Créatifs');

    foreach (['users', 'conversations', 'invoices', 'promo-codes', 'selections', 'campaigns', 'categories', 'admins'] as $ecran) {
        $this->get('/admin/'.$ecran)->assertOk();
    }

    foreach (['promo-codes', 'selections', 'campaigns', 'admins'] as $ecran) {
        $this->get('/admin/'.$ecran.'/create')->assertOk();
    }
});

it('ouvre la fiche d un creatif', function () {
    $creatif = App\Models\User::factory()->create(['login' => 'ariane']);

    // Le book est identifie par son login, jusque dans le back-office.
    $this->actingAs($this->admin, 'admin')->get('/admin/users/'.$creatif->login.'/edit')
        ->assertOk()->assertSee('ariane');
});
