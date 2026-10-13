<?php

use App\Mail\BienvenueVisiteur;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as UtilisateurGoogle;

beforeEach(function () {
    Mail::fake();
    config(['services.google.client_id' => 'id', 'services.google.client_secret' => 'secret']);
});

function retourGoogleVisiteur(string $id, string $email, bool $verifiee = true): void
{
    $google = (new UtilisateurGoogle)
        ->setRaw(['email_verified' => $verifiee, 'given_name' => 'Nolwenn', 'family_name' => 'Le Goff'])
        ->map(['id' => $id, 'email' => $email, 'name' => 'Nolwenn Le Goff']);
    $pilote = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
    $pilote->shouldReceive('redirectUrl')->andReturnSelf();
    $pilote->shouldReceive('user')->andReturn($google);
    Socialite::shouldReceive('driver')->with('google')->andReturn($pilote);
}

it('cree un compte visiteur verifie et ouvre le memo', function () {
    retourGoogleVisiteur('g-1', 'Nolwenn@Example.com');

    $this->get('/auth/google/visiteur/callback')->assertRedirect(route('memobook'));

    $visiteur = Visitor::query()->where('email', 'nolwenn@example.com')->firstOrFail();
    expect(auth('visitor')->id())->toBe($visiteur->id)
        ->and($visiteur->google_id)->toBe('g-1')
        ->and($visiteur->firstname)->toBe('Nolwenn')
        ->and($visiteur->email_verified_at)->not->toBeNull();
    Mail::assertSent(BienvenueVisiteur::class, fn ($mail) => $mail->lienConfirmation === null);
});

it('connecte un visiteur deja lie a Google', function () {
    $visiteur = Visitor::factory()->create(['google_id' => 'g-2']);
    retourGoogleVisiteur('g-2', 'autre@example.com');

    $this->get('/auth/google/visiteur/callback')->assertRedirect(route('memobook'));
    expect(auth('visitor')->id())->toBe($visiteur->id);
});

it('ne rattache pas Google a un visiteur dont l adresse n est pas confirmee', function () {
    $visiteur = Visitor::factory()->create(['email' => 'nolwenn@example.com', 'email_verified_at' => null]);
    retourGoogleVisiteur('g-3', 'nolwenn@example.com');

    $this->get('/auth/google/visiteur/callback')->assertSessionHas('memo_compte_erreur');
    expect(auth('visitor')->check())->toBeFalse()
        ->and($visiteur->fresh()->google_id)->toBeNull();
});

it('refuse l adresse d un createur', function () {
    User::factory()->create(['email' => 'creatif@example.com']);
    retourGoogleVisiteur('g-4', 'creatif@example.com');

    $this->get('/auth/google/visiteur/callback')->assertSessionHas('memo_compte_erreur');
    expect(auth('visitor')->check())->toBeFalse()
        ->and(Visitor::query()->count())->toBe(0);
});

it('ne rouvre pas un compte visiteur suspendu', function () {
    Visitor::factory()->create(['google_id' => 'g-5', 'blocked_at' => now()]);
    retourGoogleVisiteur('g-5', 'bloque@example.com');

    $this->get('/auth/google/visiteur/callback')->assertSessionHas('memo_compte_erreur');
    expect(auth('visitor')->check())->toBeFalse();
});

it('garde la photo Google du visiteur et l affiche dans son compte', function () {
    $google = (new UtilisateurGoogle)->setRaw(['email_verified' => true])
        ->map(['id' => 'g-6', 'email' => 'photo@example.com', 'avatar' => 'https://lh3.googleusercontent.com/a/abc=s96-c']);
    $pilote = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
    $pilote->shouldReceive('redirectUrl')->andReturnSelf();
    $pilote->shouldReceive('user')->andReturn($google);
    Socialite::shouldReceive('driver')->with('google')->andReturn($pilote);

    $this->get('/auth/google/visiteur/callback');

    $visiteur = Visitor::query()->where('email', 'photo@example.com')->firstOrFail();
    expect($visiteur->avatar_url)->toBe('https://lh3.googleusercontent.com/a/abc=s256-c');
    $this->get(route('visiteur.compte'))->assertOk()->assertSee('abc=s256-c', false);
});
