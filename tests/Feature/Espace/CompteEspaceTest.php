<?php

use App\Livewire\Espace\Compte;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $this->creatif = User::factory()->create(['password' => 'ancien-secret', 'email' => 'moi@example.test']);
    $this->actingAs($this->creatif);
});

it('affiche et enregistre le profil', function () {
    $this->get(route('espace.compte'))->assertOk()->assertSee('moi@example.test');

    Livewire::test(Compte::class)
        ->set('profil.city', 'Lyon')
        ->set('profil.website', 'https://exemple.fr')
        ->call('enregistrerProfil')
        ->assertHasNoErrors();

    expect($this->creatif->fresh())->city->toBe('Lyon')->website->toBe('https://exemple.fr');
});

it('refuse un lien javascript', function () {
    Livewire::test(Compte::class)->set('profil.website', 'javascript:alert(1)')->call('enregistrerProfil')
        ->assertHasErrors('profil.website');
});

it('exige le mot de passe actuel pour changer les acces', function () {
    Livewire::test(Compte::class)
        ->set('email', 'nouveau@example.test')->set('motDePasseActuel', 'faux')
        ->call('enregistrerAcces')->assertHasErrors('motDePasseActuel');

    expect($this->creatif->fresh()->email)->toBe('moi@example.test');
});

it('change le mot de passe', function () {
    Livewire::test(Compte::class)
        ->set('motDePasseActuel', 'ancien-secret')
        ->set('nouveauMotDePasse', 'nouveau-secret')
        ->set('nouveauMotDePasse_confirmation', 'nouveau-secret')
        ->call('enregistrerAcces')->assertHasNoErrors();

    expect(Hash::check('nouveau-secret', $this->creatif->fresh()->password))->toBeTrue();
});

it('refuse une adresse deja prise', function () {
    User::factory()->create(['email' => 'pris@example.test']);

    Livewire::test(Compte::class)
        ->set('email', 'pris@example.test')->set('motDePasseActuel', 'ancien-secret')
        ->call('enregistrerAcces')->assertHasErrors('email');
});
