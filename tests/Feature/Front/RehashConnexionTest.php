<?php

use App\Actions\Auth\IdentifierCompte;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/*
 * La reprise legacy hache les mots de passe a un cout reduit (vitesse de
 * conversion) : la premiere connexion les remet au cout de la config.
 */

function coutBcrypt(string $hash): int
{
    return password_get_info($hash)['options']['cost'];
}

it('remet au cout de la config le mot de passe d un compte repris, a la connexion', function () {
    $compte = User::factory()->create(['login' => 'repris']);
    $cout = config('hashing.bcrypt.rounds') + 1;
    DB::table('users')->where('id', $compte->id)
        ->update(['password' => password_hash('secret', PASSWORD_BCRYPT, ['cost' => $cout])]);

    $reconnu = (new IdentifierCompte)->executer('repris', 'secret');

    expect($reconnu)->toBeInstanceOf(User::class);
    $hash = DB::table('users')->where('id', $compte->id)->value('password');
    expect(coutBcrypt($hash))->toBe((int) config('hashing.bcrypt.rounds'))
        ->and(Hash::check('secret', $hash))->toBeTrue();
});

it('ne touche pas au mot de passe si la connexion echoue', function () {
    $compte = User::factory()->create(['login' => 'repris']);
    $ancien = password_hash('secret', PASSWORD_BCRYPT, ['cost' => config('hashing.bcrypt.rounds') + 1]);
    DB::table('users')->where('id', $compte->id)->update(['password' => $ancien]);

    expect((new IdentifierCompte)->executer('repris', 'faux'))->toBeNull()
        ->and(DB::table('users')->where('id', $compte->id)->value('password'))->toBe($ancien);
});
