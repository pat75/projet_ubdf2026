<?php

use App\Models\Admin;
use App\Models\AdminActivity;
use App\Models\PromoCode;
use App\Models\User;
use App\Services\Admin\ExportCsv;

beforeEach(function () {
    $this->admin = Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']);
    $this->actingAs($this->admin, 'admin');
});

it('exporte en CSV la liste telle qu elle est filtree', function () {
    User::factory()->create(['login' => 'ariane', 'brand' => 'ub', 'firstname' => 'Ariane', 'lastname' => 'Noé']);
    User::factory()->create(['login' => 'dustman', 'brand' => 'df']);

    // L'action du tableau passe sa propre requete filtree au service ;
    // on verifie ici que le service en tire le bon fichier.
    $reponse = app(ExportCsv::class)->reponse(
        User::where('brand', 'ub'),
        ['Identifiant' => fn (User $u) => $u->login, 'Nom' => fn (User $u) => $u->fullName()],
        'creatifs',
    );

    ob_start();
    $reponse->sendContent();
    $csv = ob_get_clean();

    expect($csv)->toContain('Identifiant;Nom')
        ->toContain('ariane;"Ariane Noé"')
        ->not->toContain('dustman')
        ->and($reponse->headers->get('content-disposition'))->toContain('creatifs-'.now()->format('Y-m-d').'.csv');
});

it('propose l export depuis la liste des creatifs', function () {
    User::factory()->create(['brand' => 'ub']);

    $this->get('/admin/users')->assertOk()->assertSee('Exporter en CSV');
    $this->get('/admin/invoices')->assertOk()->assertSee('Exporter en CSV');
});

it('journalise les modifications faites depuis le back-office', function () {
    $creatif = User::factory()->create(['login' => 'ariane', 'plan' => 0]);

    $creatif->update(['plan' => 1, 'plan_months' => 12]);

    // La creation du createur, faite elle aussi sous ce compte, est notee a part.
    $ligne = AdminActivity::where('action', 'updated')->sole();

    expect($ligne)
        ->admin_name->toBe('Pat')
        ->action->toBe('updated')
        ->subject_label->toBe('ariane')
        ->and($ligne->changes)->toHaveKeys(['plan', 'plan_months'])
        ->and($ligne->changes['plan'])->toBe(['avant' => 0, 'apres' => 1]);

    $this->get('/admin/admin-activities')->assertOk()->assertSee('ariane');
});

it('ne journalise pas ce qui vient du site', function () {
    auth('admin')->logout();

    User::factory()->create()->update(['city' => 'Lyon']);

    expect(AdminActivity::count())->toBe(0);
});

it('ne recopie jamais un mot de passe dans le journal', function () {
    $autre = Admin::create(['name' => 'Autre', 'email' => 'autre@example.test', 'password' => 'un-mot-de-passe']);

    $autre->update(['password' => 'un-autre-mot-de-passe']);

    $ligne = AdminActivity::where('action', 'updated')->sole();

    expect($ligne->changes['password'])->toBe(['avant' => '***', 'apres' => '***']);
});

it('journalise la creation d un code promo', function () {
    PromoCode::create(['code' => 'NOEL2026', 'discount' => 3, 'discount_type' => PromoCode::MOIS, 'max_uses' => 1]);

    expect(AdminActivity::where('subject_label', 'NOEL2026')->sole())->action->toBe('created');
});
