<?php

use App\Models\Admin;
use App\Models\Conversation;
use App\Models\PromoCode;
use App\Models\User;

beforeEach(function () {
    $this->admin = Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']);
});

it('demande une authentification pour entrer', function () {
    $this->get('/admin_')->assertRedirect();
    $this->get('/admin_/login')->assertOk();
});

it('refuse un compte createur', function () {
    $this->actingAs(User::factory()->create())->get('/admin_')->assertRedirect();
});

it('refuse un administrateur desactive', function () {
    $this->admin->update(['is_active' => false]);

    $this->actingAs($this->admin, 'admin')->get('/admin_')->assertForbidden();
});

it('liste les creatifs, les factures et les demandes', function () {
    $creatif = User::factory()->create(['login' => 'ariane']);
    $creatif->invoices()->create(['number' => 'ub-1', 'brand' => 'ub', 'designation' => 'Formule 12 mois',
        'amount' => 36.80, 'vat' => 6.13, 'status' => 'paid', 'issued_at' => now()]);
    Conversation::create(['user_id' => $creatif->id, 'channel' => 'book', 'sender_name' => 'Client Dupont', 'selector' => str_repeat('a', 24)]);

    $this->actingAs($this->admin, 'admin');
    $this->get('/admin_/users')->assertOk()->assertSee('ariane');
    $this->get('/admin_/invoices')->assertOk();
    $this->get('/admin_/conversations')->assertOk()->assertSee('Client Dupont');
});

it('permet de creer un code promo utilisable par un creatif', function () {
    $this->actingAs($this->admin, 'admin')->get('/admin_/promo-codes/create')->assertOk();

    PromoCode::create(['code' => 'ADMIN2026', 'discount' => 3, 'discount_type' => PromoCode::MOIS, 'max_uses' => 1]);
    $creatif = User::factory()->create(['plan' => 0]);

    expect(app(App\Services\Paiement\Parrainage::class)->utiliserCode($creatif, 'ADMIN2026'))->toContain('3 mois')
        ->and($creatif->fresh()->plan_months)->toBe(3);
});

it('ouvre chaque ecran du back-office', function () {
    $this->actingAs($this->admin, 'admin');

    $this->get('/admin_')->assertOk()->assertSee('Créatifs');

    foreach (['users', 'conversations', 'invoices', 'promo-codes', 'selections', 'campaigns', 'categories', 'admins'] as $ecran) {
        $this->get('/admin_/'.$ecran)->assertOk();
    }

    foreach (['promo-codes', 'selections', 'campaigns', 'admins'] as $ecran) {
        $this->get('/admin_/'.$ecran.'/create')->assertOk();
    }
});

it('affiche le titre et la bascule clair sombre en haut', function () {
    $this->actingAs($this->admin, 'admin')->get('/admin_/users')->assertOk()
        ->assertSee('Ultra-book classique')
        ->assertSee('fi-topbar-theme-switcher', escape: false)
        // Les retouches d'aspect (fond de la colonne de navigation)
        // arrivent par un crochet dans le <head> du panneau.
        ->assertSee('.dark .fi-sidebar', escape: false);
});

it('ouvre la fiche d un creatif', function () {
    $creatif = App\Models\User::factory()->create(['login' => 'ariane']);

    // Le book est identifie par son login, jusque dans le back-office.
    $this->actingAs($this->admin, 'admin')->get('/admin_/users/'.$creatif->login.'/edit')
        ->assertOk()->assertSee('ariane');
});
