<?php

use App\Mail\BienvenueCreatif;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as UtilisateurGoogle;

beforeEach(function () {
    Mail::fake();
    $this->seed(CategorySeeder::class);
    config(['services.google.client_id' => 'id', 'services.google.client_secret' => 'secret']);
});

function retourGoogle(string $id, string $email, string $nom = 'Nolwenn Le Goff', bool $verifiee = true): void
{
    $google = (new UtilisateurGoogle)->setRaw(['email_verified' => $verifiee])->map(['id' => $id, 'email' => $email, 'name' => $nom]);
    $pilote = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
    $pilote->shouldReceive('redirectUrl')->andReturnSelf();
    $pilote->shouldReceive('user')->andReturn($google);
    Socialite::shouldReceive('driver')->with('google')->andReturn($pilote);
}

it('connecte un compte existant par son adresse et lui rattache Google', function () {
    $compte = User::factory()->create(['email' => 'nolwenn@example.com', 'email_verified_at' => now()]);
    retourGoogle('g-123', 'nolwenn@example.com');

    $this->get('/auth/google/callback')->assertRedirect(route('espace'));

    expect(auth()->id())->toBe($compte->id)
        ->and($compte->fresh()->google_id)->toBe('g-123');
});

it('ne rattache pas Google a un compte dont l adresse n est pas confirmee', function () {
    $compte = User::factory()->create(['email' => 'nolwenn@example.com', 'email_verified_at' => null]);
    retourGoogle('g-123', 'nolwenn@example.com');

    $this->get('/auth/google/callback')->assertSessionHasErrors('login');

    expect(auth()->check())->toBeFalse()
        ->and($compte->fresh()->google_id)->toBeNull();
});

it('ne rattache pas Google quand Google ne garantit pas l adresse', function () {
    $compte = User::factory()->create(['email' => 'nolwenn@example.com', 'email_verified_at' => now()]);
    retourGoogle('g-123', 'nolwenn@example.com', verifiee: false);

    $this->get('/auth/google/callback')->assertSessionHasErrors('login');

    expect(auth()->check())->toBeFalse()
        ->and($compte->fresh()->google_id)->toBeNull();
});

it('refuse de choisir entre plusieurs books de la meme adresse', function () {
    User::factory()->count(2)->create(['email' => 'partage@example.com']);
    retourGoogle('g-456', 'partage@example.com');

    $this->get('/auth/google/callback')->assertSessionHasErrors('login');
    expect(auth()->check())->toBeFalse();
});

it('cree un book avec un mot de passe de secours', function () {
    retourGoogle('g-789', 'nouveau@example.com');

    $this->get('/auth/google/callback')->assertRedirect(route('inscription.page'));
    $this->get(route('inscription.page'))->assertOk()->assertSee('nouveau@example.com');

    $this->post('/auth/google/inscription', [
        'us_login' => 'nouveau',
        'us_type' => 'graphiste',
        'us_licence' => '1',
    ])->assertRedirect(route('espace'));

    $compte = User::query()->where('login', 'nouveau')->firstOrFail();
    expect($compte->google_id)->toBe('g-789')
        ->and($compte->email)->toBe('nouveau@example.com')
        ->and($compte->email_verified_at)->not->toBeNull()
        ->and($compte->password)->not->toBeEmpty()
        ->and(auth()->id())->toBe($compte->id);
    Mail::assertSent(BienvenueCreatif::class);
});

it('refuse la fin d inscription sans passage par Google', function () {
    $this->post('/auth/google/inscription', ['us_login' => 'intrus', 'us_licence' => '1'])->assertForbidden();
});

it('laisse la connexion par mot de passe a un compte cree par Google', function () {
    User::factory()->create(['login' => 'viagoogle', 'google_id' => 'g-1', 'password' => Hash::make('choisi-plus-tard')]);

    $this->post('/ubaction__user_open', ['login' => 'viagoogle', 'pass' => 'choisi-plus-tard'])
        ->assertRedirect(route('espace'));
});
