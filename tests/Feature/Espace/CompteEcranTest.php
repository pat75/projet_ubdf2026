<?php

use App\Livewire\Espace\Compte;
use App\Models\User;
use Livewire\Livewire;

/*
 | L'ecran « Mon compte », repris de la capture de l'espace d'origine :
 | l'en-tete illustre, le rappel du mode de connexion, les sections
 | depliables et les champs de la fiche.
 */

it('montre l en-tete, le rappel de connexion et les sections', function () {
    $this->actingAs(User::factory()->create())->get(route('espace.compte'))
        ->assertOk()
        ->assertSeeInOrder([
            'Mes informations',
            'publiques et privées',
            'Votre connexion s’est faite via votre identifiant et votre mot de passe',
            'Votre compte',
            'Url de votre book',
            'Identifiant / Login',
            'Métier',
            'Votre statut',
            'Contacts par SMS',
            'Localisation',
        ], escape: false);
});

it('propose les statuts professionnels du legacy', function () {
    $creatif = User::factory()->create(['status' => 'Freelance']);

    Livewire::actingAs($creatif)->test(Compte::class)
        ->assertSet('statut', 'Freelance')
        ->set('statut', 'Maison des artistes')
        ->call('enregistrerProfil');

    expect($creatif->fresh()->status)->toBe('Maison des artistes');
});

it('ignore un statut herite du legacy sous forme de nombre', function () {
    // La reprise a laisse des indices bruts dans quelques fiches : on ne
    // les affiche pas comme si c'etait un libelle.
    $creatif = User::factory()->create(['status' => '6']);

    Livewire::actingAs($creatif)->test(Compte::class)->assertSet('statut', '');
});

it('refuse un statut hors liste', function () {
    $creatif = User::factory()->create();

    Livewire::actingAs($creatif)->test(Compte::class)
        ->set('statut', 'Pirate')
        ->call('enregistrerProfil')
        ->assertHasErrors('statut');
});

it('enregistre le consentement aux SMS', function () {
    $creatif = User::factory()->create(['accepts_sms' => false]);

    Livewire::actingAs($creatif)->test(Compte::class)
        ->set('sms', true)
        ->call('enregistrerProfil');

    expect($creatif->fresh()->accepts_sms)->toBeTrue();
});

it('supprime le portfolio quand le createur recopie son identifiant', function () {
    $creatif = User::factory()->create(['login' => 'aline', 'password' => 'mot-de-passe-long']);

    Livewire::actingAs($creatif)->test(Compte::class)
        ->set('confirmationSuppression', 'aline')
        ->set('motDePasseActuel', 'mot-de-passe-long')
        ->call('supprimerPortfolio');

    // Suppression douce : la fiche quitte le site, la ligne reste.
    expect(User::where('login', 'aline')->exists())->toBeFalse()
        ->and(User::withTrashed()->where('login', 'aline')->exists())->toBeTrue()
        ->and(auth()->check())->toBeFalse();
});

it('ne supprime rien si l identifiant ou le mot de passe ne suit pas', function () {
    $creatif = User::factory()->create(['login' => 'aline', 'password' => 'mot-de-passe-long']);

    Livewire::actingAs($creatif)->test(Compte::class)
        ->set('confirmationSuppression', 'alin')
        ->set('motDePasseActuel', 'mot-de-passe-long')
        ->call('supprimerPortfolio')
        ->assertHasErrors('confirmationSuppression');

    Livewire::actingAs($creatif)->test(Compte::class)
        ->set('confirmationSuppression', 'aline')
        ->set('motDePasseActuel', 'au-hasard')
        ->call('supprimerPortfolio')
        ->assertHasErrors('motDePasseActuel');

    expect(User::where('login', 'aline')->exists())->toBeTrue();
});
