<?php

use App\Filament\Pages\RelanceAbonnementEchu;
use App\Models\Admin;
use App\Models\User;
use App\Services\Paiement\Relances;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('liste les echus avec login, mail, nombre de relances et abonnements repris', function () {
    Mail::fake();
    $this->actingAs(Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']), 'admin');

    $echu = User::factory()->create([
        'login' => 'echu1', 'email' => 'echu1@example.test', 'plan' => 1,
        'plan_started_at' => now()->subMonths(13), 'plan_months' => 12,
    ]);
    app(Relances::class)->relancerEchu($echu);

    Livewire::test(RelanceAbonnementEchu::class)
        ->removeTableFilter('relancables')
        ->assertCanSeeTableRecords([$echu])
        ->assertSee('echu1@example.test')
        ->assertSee('0 abonnement repris');
});

it('compte les relances deja envoyees pour l echeance en cours', function () {
    Mail::fake();
    $this->actingAs(Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']), 'admin');

    $echu = User::factory()->create([
        'login' => 'echu2', 'email' => 'echu2@example.test', 'plan' => 1,
        'plan_started_at' => now()->subMonths(13)->setTime(9, 47), 'plan_months' => 12,
    ]);
    $relances = app(Relances::class);
    $relances->relancerEchu($echu);
    $this->travel(8)->days();
    $relances->relancerEchu($echu);

    Livewire::test(RelanceAbonnementEchu::class)
        ->assertCanSeeTableRecords([$echu])
        ->assertTableColumnStateSet('nb_relances', 2, record: $echu);
});
