<?php

use App\Livewire\Espace\Compte;
use App\Mail\ConfirmationAdresse;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->creatif = User::factory()->create(['password' => 'ancien-secret', 'email' => 'moi@example.test']);
    $this->actingAs($this->creatif);
});

it('affiche et enregistre le profil', function () {
    $this->get(route('espace.compte'))->assertOk()->assertSee('moi@example.test');

    // Edition sur place : chaque champ part seul, a la sortie du champ.
    Livewire::test(Compte::class)
        ->call('enregistrerChamp', 'city', '  Lyon ')
        ->assertReturned(['ok' => true])
        ->assertSet('profil.city', 'Lyon')
        ->call('enregistrerChamp', 'website', 'https://exemple.fr')
        ->assertReturned(['ok' => true]);

    expect($this->creatif->fresh())->city->toBe('Lyon')->website->toBe('https://exemple.fr');
});

it('vide un champ en base quand le texte est efface', function () {
    $this->creatif->update(['company' => 'Atelier']);

    Livewire::test(Compte::class)->call('enregistrerChamp', 'company', '');

    expect($this->creatif->fresh()->company)->toBeNull();
});

it('refuse un lien javascript', function () {
    $reponse = Livewire::test(Compte::class)->call('enregistrerChamp', 'website', 'javascript:alert(1)')->effects['returns'][0];

    expect($reponse)->toHaveKey('erreur')
        ->and($this->creatif->fresh()->website)->not->toBe('javascript:alert(1)');
});

it('refuse d enregistrer par ce biais un champ hors de la liste', function () {
    Livewire::test(Compte::class)->call('enregistrerChamp', 'category_id', '3')->assertStatus(422);
});

it('ne change l adresse mail qu apres le lien de confirmation', function () {
    Mail::fake();

    Livewire::test(Compte::class)
        ->call('enregistrerChamp', 'email', 'nouveau@example.test')
        ->assertReturned(['ok' => true]);

    expect($this->creatif->fresh()->email)->toBe('moi@example.test');

    $lien = null;
    Mail::assertSent(ConfirmationAdresse::class, function (ConfirmationAdresse $mail) use (&$lien) {
        $lien = $mail->lienConfirmation;

        return $mail->hasTo('nouveau@example.test');
    });

    $this->get($lien)->assertRedirect();

    expect($this->creatif->fresh())
        ->email->toBe('nouveau@example.test')
        ->email_verified_at->not->toBeNull();
});

it('refuse un lien de changement d adresse falsifie ou perime', function () {
    Mail::fake();

    Livewire::test(Compte::class)->call('enregistrerChamp', 'email', 'nouveau@example.test');

    $lien = null;
    Mail::assertSent(ConfirmationAdresse::class, function (ConfirmationAdresse $mail) use (&$lien) {
        $lien = $mail->lienConfirmation;

        return true;
    });

    $this->get(str_replace('nouveau%40', 'pirate%40', $lien))->assertForbidden();

    // L'adresse a change depuis : l'ancien lien ne vaut plus rien.
    $this->creatif->forceFill(['email' => 'autre@example.test'])->save();
    $this->get($lien)->assertForbidden();

    expect($this->creatif->fresh()->email)->toBe('autre@example.test');
});

it('refuse une adresse mail deja prise', function () {
    User::factory()->create(['email' => 'pris@example.test']);

    $reponse = Livewire::test(Compte::class)->call('enregistrerChamp', 'email', 'pris@example.test')->effects['returns'][0];

    expect($reponse)->toHaveKey('erreur')
        ->and($this->creatif->fresh()->email)->toBe('moi@example.test');
});

it('change le mot de passe, sans mot de passe actuel ni confirmation', function () {
    Livewire::test(Compte::class)
        ->call('enregistrerChamp', 'motDePasse', 'nouveau-secret')
        ->assertReturned(['ok' => true]);

    expect(Hash::check('nouveau-secret', $this->creatif->fresh()->password))->toBeTrue();
});

it('refuse un mot de passe trop court', function () {
    app()->setLocale('fr');

    $reponse = Livewire::test(Compte::class)->call('enregistrerChamp', 'motDePasse', 'court')->effects['returns'][0];

    // Un message lisible, pas la cle brute « validation.min.string ».
    expect($reponse['erreur'])->toBe('Le champ mot de passe doit contenir au moins 8 caractères.')
        ->and(Hash::check('court', $this->creatif->fresh()->password))->toBeFalse();
});

it('abonne et desabonne de la newsletter', function () {
    $this->creatif->bookSetting()->updateOrCreate([], ['diffuse_newsletter' => true]);

    $this->get(route('espace.compte'))->assertOk()->assertSee('Recevoir la newsletter');

    Livewire::test(Compte::class)
        ->assertSet('newsletter', true)
        ->toggle('newsletter');

    expect($this->creatif->bookSetting()->first()->diffuse_newsletter)->toBeFalse();

    Livewire::test(Compte::class)->assertSet('newsletter', false)->toggle('newsletter');

    expect($this->creatif->bookSetting()->first()->diffuse_newsletter)->toBeTrue();
});
