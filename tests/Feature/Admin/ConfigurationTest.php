<?php

use App\Filament\Pages\ConfigurationPage;
use App\Models\Admin;
use App\Models\Reglage;
use Livewire\Livewire;

beforeEach(function () {
    config(['marques.marques.ub.hotes' => ['ubdf2026.ultra-book.name']]);
    config(['services.google.client_id' => 'id', 'services.google.client_secret' => 'secret']);
    $this->portail = 'https://ubdf2026.ultra-book.name';
});

it('propose Google par defaut', function () {
    expect(Reglage::googleActif())->toBeTrue();

    $this->get($this->portail.'/creer-un-book')->assertSee('S’inscrire avec Google');
});

it('masque Google depuis la page Configuration', function () {
    $this->actingAs(Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']), 'admin');

    Livewire::test(ConfigurationPage::class)
        ->assertSet('data.google', true)
        ->set('data.google', false)
        ->call('save');

    expect(Reglage::googleActif())->toBeFalse();

    $this->get($this->portail.'/creer-un-book')
        ->assertOk()
        ->assertDontSee('S’inscrire avec Google')
        ->assertDontSee('Continuer avec Google')
        ->assertDontSee('ou avec votre e-mail');
});

it('refuse les adresses Google quand elles sont masquees', function () {
    Reglage::definir(Reglage::GOOGLE_MASQUE, true);

    $this->get($this->portail.'/auth/google')->assertSessionHasErrors('login');
    $this->get($this->portail.'/auth/google/callback')->assertSessionHasErrors('login');
});
