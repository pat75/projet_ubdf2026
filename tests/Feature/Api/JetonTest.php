<?php

use App\Models\User;
use App\Models\Visitor;
use Laravel\Sanctum\PersonalAccessToken;

/*
 * Connexion des applications (mobile, desktop) : un jeton par appareil,
 * limite aux droits demandes, revocable.
 */

function creatifApi(array $attributs = []): User
{
    return User::factory()->create(['login' => 'jeanne', 'password' => 'secret'] + $attributs);
}

it('delivre un jeton au creatif qui se connecte par son identifiant', function () {
    creatifApi();

    $reponse = $this->postJson('/api/v1/jetons', [
        'identifiant' => 'jeanne', 'mot_de_passe' => 'secret', 'appareil' => 'iPhone de Jeanne',
    ]);

    $reponse->assertCreated()->assertJsonStructure(['jeton', 'droits']);
    expect(PersonalAccessToken::first()->name)->toBe('iPhone de Jeanne');
});

it('limite le jeton aux droits demandes', function () {
    creatifApi();

    $this->postJson('/api/v1/jetons', [
        'identifiant' => 'jeanne', 'mot_de_passe' => 'secret', 'appareil' => 'Mac',
        'droits' => ['images:ecrire'],
    ])->assertCreated()->assertJsonPath('droits', ['images:ecrire']);

    expect(PersonalAccessToken::first()->abilities)->toBe(['images:ecrire']);
});

it('refuse un droit inconnu', function () {
    creatifApi();

    $this->postJson('/api/v1/jetons', [
        'identifiant' => 'jeanne', 'mot_de_passe' => 'secret', 'appareil' => 'Mac',
        'droits' => ['*'],
    ])->assertUnprocessable()->assertJsonValidationErrors('droits.0');
});

it('refuse un mauvais mot de passe', function () {
    creatifApi();

    $this->postJson('/api/v1/jetons', [
        'identifiant' => 'jeanne', 'mot_de_passe' => 'faux', 'appareil' => 'Mac',
    ])->assertUnprocessable()->assertJsonValidationErrors('identifiant');

    expect(PersonalAccessToken::count())->toBe(0);
});

it('refuse un compte bloque', function () {
    creatifApi(['blocked_at' => now()]);

    $this->postJson('/api/v1/jetons', [
        'identifiant' => 'jeanne', 'mot_de_passe' => 'secret', 'appareil' => 'Mac',
    ])->assertForbidden();
});

it('refuse un compte visiteur : l api est reservee aux creatifs', function () {
    Visitor::factory()->create(['email' => 'v@exemple.fr', 'password' => 'secret']);

    $this->postJson('/api/v1/jetons', [
        'identifiant' => 'v@exemple.fr', 'mot_de_passe' => 'secret', 'appareil' => 'Mac',
    ])->assertUnprocessable();
});

it('rend le profil du porteur du jeton', function () {
    $creatif = creatifApi();
    $jeton = $creatif->createToken('Mac', ['compte:lire'])->plainTextToken;

    $this->withToken($jeton)->getJson('/api/v1/moi')
        ->assertOk()
        ->assertJsonPath('data.login', 'jeanne')
        ->assertJsonMissingPath('data.password');
});

it('refuse le profil sans le droit compte:lire', function () {
    $jeton = creatifApi()->createToken('Mac', ['images:ecrire'])->plainTextToken;

    $this->withToken($jeton)->getJson('/api/v1/moi')->assertForbidden();
});

it('refuse un jeton absent', function () {
    $this->getJson('/api/v1/moi')->assertUnauthorized();
});

it('revoque le jeton courant a la deconnexion', function () {
    $jeton = creatifApi()->createToken('Mac', ['compte:lire'])->plainTextToken;

    $this->withToken($jeton)->deleteJson('/api/v1/jetons/courant')->assertNoContent();

    expect(PersonalAccessToken::count())->toBe(0);
});

it('coupe un jeton des que le compte est bloque', function () {
    $creatif = creatifApi();
    $jeton = $creatif->createToken('Mac', ['compte:lire'])->plainTextToken;
    $creatif->bloquer();

    $this->withToken($jeton)->getJson('/api/v1/moi')->assertUnauthorized();
});

it('ne laisse aucun site tiers appeler l api depuis un navigateur', function () {
    $this->withHeaders(['Origin' => 'https://pirate.exemple', 'Access-Control-Request-Method' => 'GET'])
        ->options('/api/v1/moi')
        ->assertHeaderMissing('Access-Control-Allow-Origin');
});

it('borne les essais de mot de passe par identifiant, quelle que soit l IP', function () {
    creatifApi();

    foreach (range(1, 10) as $i) {
        $this->withServerVariables(['REMOTE_ADDR' => "10.0.0.{$i}"])
            ->postJson('/api/v1/jetons', ['identifiant' => 'jeanne', 'mot_de_passe' => 'faux', 'appareil' => 'Mac']);
    }

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.1.1'])
        ->postJson('/api/v1/jetons', ['identifiant' => 'jeanne', 'mot_de_passe' => 'secret', 'appareil' => 'Mac'])
        ->assertStatus(429);
});
