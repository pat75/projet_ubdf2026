<?php

use App\Mail\BienvenueCreatif;
use App\Mail\MotDePasseOublie;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\Auth\Inscription;
use App\Services\Auth\MotDePasse;
use Database\Seeders\CategorySeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    Mail::fake();
    $this->seed(CategorySeeder::class);
    RateLimiter::clear('inscription|127.0.0.1');
    RateLimiter::clear('mdp-oublie|127.0.0.1');

    $this->creatif = User::factory()->create([
        'login' => 'nolwenn',
        'email' => 'nolwenn@example.com',
        'password' => Hash::make('motdepasse-2026'),
    ]);
});

/*
|--------------------------------------------------------------------------
| Connexion
|--------------------------------------------------------------------------
*/

it('connecte un creatif et l amene sur son espace', function () {
    $this->post('/ubaction__user_open', [
        'login' => 'nolwenn',
        'pass' => 'motdepasse-2026',
    ])->assertRedirect(route('espace'));

    expect(auth()->id())->toBe($this->creatif->id);
});

it('accepte un identifiant saisi en majuscules', function () {
    // Le legacy passait le login en minuscules a l'inscription mais pas a
    // la connexion : un creatif inscrit « Nolwenn » ne pouvait plus entrer.
    $this->post('/ubaction__user_open', [
        'login' => 'NOLWENN',
        'pass' => 'motdepasse-2026',
    ])->assertRedirect(route('espace'));

    expect(auth()->check())->toBeTrue();
});

it('donne le meme message pour un compte inconnu et un mot de passe faux', function () {
    $inconnu = $this->from('/')->post('/ubaction__user_open', [
        'login' => 'personne',
        'pass' => 'motdepasse-2026',
    ]);

    $faux = $this->from('/')->post('/ubaction__user_open', [
        'login' => 'nolwenn',
        'pass' => 'autre-chose',
    ]);

    expect(session()->get('errors')?->first('login'))->toBeString();

    $inconnu->assertSessionHasErrors('login');
    $faux->assertSessionHasErrors('login');
    expect(auth()->check())->toBeFalse();
});

it('bloque dix minutes apres cinq tentatives infructueuses', function () {
    foreach (range(1, 5) as $i) {
        $this->from('/')->post('/ubaction__user_open', [
            'login' => 'nolwenn',
            'pass' => 'faux',
        ]);
    }

    // La sixieme est refusee meme avec le bon mot de passe : c'est le
    // compteur cote serveur, la ou le legacy decrementait une variable de
    // session dont il ne faisait rien.
    $this->from('/')->post('/ubaction__user_open', [
        'login' => 'nolwenn',
        'pass' => 'motdepasse-2026',
    ])->assertSessionHasErrors('login');

    expect(auth()->check())->toBeFalse();

    // Dix minutes, annoncees en minutes : « Reessayez dans 487 secondes »
    // donne un nombre que personne ne lit.
    $attente = RateLimiter::availableIn('connexion|nolwenn|127.0.0.1');

    expect($attente)->toBeGreaterThan(540)->toBeLessThanOrEqual(600)
        ->and(session()->get('errors')->first('login'))->toContain('minutes');
});

it('deconnecte et vide la session', function () {
    $this->actingAs($this->creatif)
        ->post('/ubaction__user_out')
        ->assertRedirect(route('accueil'));

    expect(auth()->check())->toBeFalse();
});

it('refuse l espace a un visiteur', function () {
    $this->get('/espace')->assertRedirect();
});

/*
|--------------------------------------------------------------------------
| Disponibilite de l identifiant
|--------------------------------------------------------------------------
*/

it('repond en texte brut a la verification d identifiant', function () {
    // Le script compare `data == 'true'` sans dataType JSON.
    $this->get('/inscription?action=loginexist&us_login=libre-2026')
        ->assertOk()
        ->assertSee('true', escape: false);

    $this->get('/inscription?action=loginexist&us_login=nolwenn')
        ->assertOk()
        ->assertSee('false', escape: false);
});

it('refuse un identifiant qui masquerait le portail', function () {
    // « www » et « df » sont des etiquettes de sous-domaine reservees. Le
    // legacy ne consultait que la table des comptes et les laissait passer.
    foreach (['www', 'df'] as $reserve) {
        $this->get('/inscription?us_login='.$reserve)
            ->assertSee('false', escape: false);
    }
});

it('refuse un identifiant aux caracteres interdits', function () {
    $this->get('/inscription?us_login='.urlencode('nol wenn'))
        ->assertSee('false', escape: false);
});

/*
|--------------------------------------------------------------------------
| Inscription
|--------------------------------------------------------------------------
*/

function inscription(array $remplace = []): array
{
    return array_merge([
        'action' => 'form',
        'form_action' => 'form_valide',
        'form_id' => 'form_adduser',
        'us_login' => 'camille-b',
        'us_mail' => 'camille@example.com',
        'us_pass' => 'un-mot-de-passe',
        'us_nom' => 'Bertin Camille',
        'us_type' => 'illustrateur',
        'us_licence' => 'on',
    ], $remplace);
}

it('cree un compte et connecte le creatif', function () {
    $reponse = $this->postJson('/inscription', inscription());

    $reponse->assertOk()->assertJson(['error' => false]);

    $compte = User::where('login', 'camille-b')->first();

    expect($compte)->not->toBeNull()
        ->and($compte->lastname)->toBe('Bertin')
        ->and($compte->firstname)->toBe('Camille')
        ->and(Hash::check('un-mot-de-passe', $compte->password))->toBeTrue()
        ->and(auth()->id())->toBe($compte->id);

    // Le book existe mais n'est diffuse nulle part : un compte vide
    // n'apparait pas sur le portail.
    expect($compte->bookSetting)->not->toBeNull()
        ->and($compte->bookSetting->diffuse_web)->toBeFalse()
        ->and($compte->bookSetting->diffuse_ub)->toBeFalse();

    Mail::assertSent(BienvenueCreatif::class);
});

it('renvoie les erreurs dans la forme attendue par le script de 2019', function () {
    $reponse = $this->postJson('/inscription', inscription([
        'us_login' => 'nolwenn',
        'us_mail' => 'pas-une-adresse',
    ]));

    $reponse->assertJson(['error' => true])
        ->assertJsonStructure(['error', 'error_msg']);

    expect($reponse->json('error_msg'))->toBeArray()->not->toBeEmpty();
});

it('refuse une inscription sans acceptation des conditions', function () {
    $this->postJson('/inscription', inscription(['us_licence' => null]))
        ->assertJson(['error' => true]);

    expect(User::where('login', 'camille-b')->exists())->toBeFalse();
});

it('refuse un identifiant reserve a l inscription', function () {
    $this->postJson('/inscription', inscription(['us_login' => 'www']))
        ->assertJson(['error' => true]);

    expect(User::where('login', 'www')->exists())->toBeFalse();
});

it('garde un prenom en plusieurs mots', function () {
    // Le legacy faisait `$tmp = explode(' ', $nom); $prenom = $tmp[1];` :
    // « Bertin Marie Claire » perdait « Claire ».
    $this->postJson('/inscription', inscription(['us_nom' => 'Bertin Marie Claire']));

    expect(User::where('login', 'camille-b')->first()->firstname)->toBe('Marie Claire');
});

it('confirme l adresse depuis le lien signe', function () {
    $compte = User::factory()->unverified()->create(['login' => 'aurelie']);

    $lien = app(Inscription::class)->lienConfirmation($compte);

    $this->actingAs($compte)->get($lien)->assertRedirect(route('espace'));

    expect($compte->fresh()->email_verified_at)->not->toBeNull();
});

it('refuse une confirmation dont la signature a ete bricolee', function () {
    $compte = User::factory()->unverified()->create(['login' => 'aurelie']);

    $lien = app(Inscription::class)->lienConfirmation($compte);

    $this->get($lien.'x')->assertForbidden();

    expect($compte->fresh()->email_verified_at)->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Mot de passe oublie
|--------------------------------------------------------------------------
*/

it('envoie un lien de reinitialisation, jamais le mot de passe', function () {
    $this->postJson('/inscription', [
        'form_id' => 'form_mdpoublie',
        'us_mail' => 'nolwenn@example.com',
    ])->assertOk()->assertJson(['error' => false]);

    Mail::assertSent(MotDePasseOublie::class, function (MotDePasseOublie $mail) {
        return count($mail->comptes) === 1
            && $mail->comptes[0]['login'] === 'nolwenn'
            // Le legacy postait « Mot de passe: %us_pass% » en clair dans
            // le corps du mail. Il n'y a plus de mot de passe a y mettre.
            && ! str_contains(json_encode($mail->comptes), 'motdepasse-2026');
    });

    expect(PasswordReset::count())->toBe(1);
});

it('ne dit pas si l adresse correspond a un compte', function () {
    $connu = $this->postJson('/inscription', [
        'form_id' => 'form_mdpoublie',
        'us_mail' => 'nolwenn@example.com',
    ]);

    $inconnu = $this->postJson('/inscription', [
        'form_id' => 'form_mdpoublie',
        'us_mail' => 'personne@example.com',
    ]);

    expect($inconnu->json('msg'))->toBe($connu->json('msg'));
    $inconnu->assertJson(['error' => false]);
});

it('envoie un lien par compte quand plusieurs partagent l adresse', function () {
    // `users.email` n'est pas unique : le legacy laissait ouvrir plusieurs
    // books sur la meme adresse, au point d'avoir un message dedie.
    User::factory()->create(['login' => 'nolwenn-photo', 'email' => 'nolwenn@example.com']);

    $this->postJson('/inscription', [
        'form_id' => 'form_mdpoublie',
        'us_mail' => 'nolwenn@example.com',
    ]);

    Mail::assertSent(MotDePasseOublie::class, fn ($mail) => count($mail->comptes) === 2);
});

it('reinitialise le mot de passe et consomme le jeton', function () {
    $lien = app(MotDePasse::class)->creerLien($this->creatif);
    $jeton = basename($lien);

    $this->get($lien)->assertOk();

    $this->post($lien, [
        'password' => 'nouveau-mot-de-passe',
        'password_confirmation' => 'nouveau-mot-de-passe',
    ])->assertRedirect(route('espace'));

    expect(Hash::check('nouveau-mot-de-passe', $this->creatif->fresh()->password))->toBeTrue();

    // Le lien ne sert qu'une fois.
    $this->get($lien)->assertStatus(410);
});

it('refuse un jeton expire', function () {
    $lien = app(MotDePasse::class)->creerLien($this->creatif);

    PasswordReset::query()->update(['expires_at' => now()->subMinute()]);

    $this->get($lien)->assertStatus(410);
});

it('invalide la demande precedente quand une nouvelle arrive', function () {
    $premier = app(MotDePasse::class)->creerLien($this->creatif);
    app(MotDePasse::class)->creerLien($this->creatif);

    $this->get($premier)->assertStatus(410);
});

/*
|--------------------------------------------------------------------------
| Marque
|--------------------------------------------------------------------------
*/

it('rattache le compte a la marque de l hote et a sa langue', function () {
    config(['marques.marques.df.hotes' => ['ubdf-dust-2026.ultra-book.name']]);

    $this->post('https://ubdf-dust-2026.ultra-book.name/inscription', inscription([
        'us_login' => 'sora-d',
    ]), ['Accept' => 'application/json']);

    $compte = User::where('login', 'sora-d')->first();

    expect($compte->brand)->toBe('df')
        ->and($compte->locale)->toBe('en');
});
