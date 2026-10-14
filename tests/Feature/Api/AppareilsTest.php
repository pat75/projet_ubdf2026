<?php

use App\Livewire\Espace\Appareils;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Livewire;

beforeEach(function () {
    $this->creatif = User::factory()->create();
});

it('connecte une application desktop par code valide sur le site', function () {
    $demande = $this->postJson('/api/v1/appareils/code', ['appareil' => 'Mac de Jeanne', 'droits' => ['images:ecrire']])
        ->assertCreated()->json();

    expect($demande['code'])->toMatch('/^[A-Z2-9]{4}-[A-Z2-9]{4}$/');

    $this->postJson('/api/v1/appareils/jeton', ['code_appareil' => $demande['code_appareil']])
        ->assertStatus(202)->assertJsonPath('statut', 'en_attente');

    // Saisi en minuscules, sans tiret : meme code.
    $this->actingAs($this->creatif);
    Livewire::test(Appareils::class)
        ->set('code', strtolower(str_replace('-', ' ', $demande['code'])))
        ->call('verifier')
        ->assertSee('« Mac de Jeanne » demande l’accès à votre compte.')
        ->assertSee('images:ecrire');

    // Rien n'est accorde avant la confirmation.
    $this->postJson('/api/v1/appareils/jeton', ['code_appareil' => $demande['code_appareil']])->assertStatus(202);

    Livewire::test(Appareils::class)
        ->set('code', $demande['code'])
        ->call('verifier')
        ->call('autoriser')
        ->assertHasNoErrors()
        ->assertSee('Mac de Jeanne est connecté.');

    $this->postJson('/api/v1/appareils/jeton', ['code_appareil' => $demande['code_appareil']])
        ->assertCreated()->assertJsonPath('droits', ['images:ecrire']);

    expect(PersonalAccessToken::sole())->name->toBe('Mac de Jeanne')->tokenable_id->toBe($this->creatif->id);

    // Le code ne sert qu'une fois.
    $this->postJson('/api/v1/appareils/jeton', ['code_appareil' => $demande['code_appareil']])->assertStatus(410);
});

it('refuse un code inconnu', function () {
    $this->actingAs($this->creatif);

    Livewire::test(Appareils::class)->set('code', 'ZZZZ-9999')->call('verifier')->assertHasErrors('code')->assertSet('demande', null);
});

it('repond expire pour un code appareil inconnu', function () {
    $this->postJson('/api/v1/appareils/jeton', ['code_appareil' => str_repeat('x', 64)])->assertStatus(410);
});

it('liste et deconnecte les appareils du creatif seulement', function () {
    $this->creatif->createToken('iPhone', ['images:lire']);
    $autre = User::factory()->create()->createToken('Pirate', ['images:lire'])->accessToken;
    $this->actingAs($this->creatif);

    $composant = Livewire::test(Appareils::class)->assertSee('iPhone')->assertDontSee('Pirate');

    $composant->call('revoquer', $autre->id);
    expect(PersonalAccessToken::count())->toBe(2);

    $composant->call('revoquer', $this->creatif->tokens()->sole()->id)->assertSee('Aucune application connectée.');
    expect(PersonalAccessToken::count())->toBe(1);
});

it('affiche les appareils dans Mon compte', function () {
    $this->actingAs($this->creatif)->get(route('espace.compte'))->assertOk()->assertSee('Appareils connectés');
});
