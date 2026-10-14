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

function retourGoogle(string $id, string $email, string $nom = 'Nolwenn Le Goff', bool $verifiee = true, ?string $avatar = null): void
{
    $google = (new UtilisateurGoogle)->setRaw(['email_verified' => $verifiee])->map(['id' => $id, 'email' => $email, 'name' => $nom, 'avatar' => $avatar]);
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

it('reprend la photo Google comme avatar du book, en grand', function () {
    $image = \Illuminate\Http\UploadedFile::fake()->image('g.jpg', 200, 200);
    \Illuminate\Support\Facades\Http::fake(['lh3.googleusercontent.com/*' => \Illuminate\Support\Facades\Http::response(file_get_contents($image->getRealPath()))]);
    retourGoogle('g-av', 'photo@example.com', avatar: 'https://lh3.googleusercontent.com/a/abc=s96-c');

    $this->get('/auth/google/callback');
    $this->post('/auth/google/inscription', ['us_login' => 'photo', 'us_type' => 'graphiste', 'us_licence' => '1']);

    $compte = User::query()->where('login', 'photo')->firstOrFail();
    $thumbnail = $compte->bookSetting->thumbnail;
    expect($thumbnail)->not->toBeNull()
        ->and(is_file(\App\Support\DossierBook::chemin('photo', $thumbnail)))->toBeTrue();
    \Illuminate\Support\Facades\Http::assertSent(fn ($r) => str_ends_with($r->url(), '=s1024-c'));
});

it('ignore une photo hors des serveurs Google', function () {
    \Illuminate\Support\Facades\Http::fake();
    retourGoogle('g-ev', 'autre@example.com', avatar: 'http://169.254.169.254/latest');

    $this->get('/auth/google/callback');
    $this->post('/auth/google/inscription', ['us_login' => 'autre', 'us_type' => 'graphiste', 'us_licence' => '1'])
        ->assertRedirect(route('espace'));

    expect(User::query()->where('login', 'autre')->firstOrFail()->bookSetting->thumbnail)->toBeNull();
    \Illuminate\Support\Facades\Http::assertNothingSent();
});

it('repond en JSON a l envoi en fetch, pour l ecran de creation en cours', function () {
    retourGoogle('g-js', 'fetch@example.com');
    $this->get('/auth/google/callback');

    $this->postJson('/auth/google/inscription', ['us_login' => 'fetch', 'us_licence' => '1'])
        ->assertJson(['error' => false])
        ->assertJsonStructure(['url_domaine', 'url_action']);
    expect(User::query()->where('login', 'fetch')->exists())->toBeTrue();
});

it('refuse en JSON une adresse de book invalide, sans perdre le passage par Google', function () {
    retourGoogle('g-js2', 'fetch2@example.com');
    $this->get('/auth/google/callback');

    $this->postJson('/auth/google/inscription', ['us_login' => 'x', 'us_licence' => '1'])
        ->assertStatus(422)->assertJson(['error' => true]);
    expect(session()->has('google.inscription'))->toBeTrue();
});

it('recree un book avec le compte Google d un book supprime', function () {
    $ancien = User::factory()->create(['login' => 'repris', 'email' => 'repris@example.com', 'google_id' => 'g-rep']);
    $ancien->delete();
    expect($ancien->fresh()->google_id)->toBeNull();

    retourGoogle('g-rep', 'repris@example.com');
    $this->get('/auth/google/callback')->assertRedirect(route('inscription.page'));
    $this->postJson('/auth/google/inscription', ['us_login' => 'repris', 'us_licence' => '1'])
        ->assertJson(['error' => false]);

    expect(User::query()->where('login', 'repris')->value('google_id'))->toBe('g-rep');
});

it('ne rouvre pas un compte suspendu par Google', function () {
    $lie = User::factory()->create(['email' => 'lie@example.com', 'google_id' => 'g-blq', 'blocked_at' => now()]);
    retourGoogle('g-blq', 'lie@example.com');
    $this->get('/auth/google/callback')->assertSessionHasErrors('login')->assertSessionHas('connexion_suspendue');
    expect(auth()->check())->toBeFalse();

    $parAdresse = User::factory()->create(['email' => 'adr@example.com', 'email_verified_at' => now(), 'blocked_at' => now()]);
    retourGoogle('g-adr', 'adr@example.com');
    $this->get('/auth/google/callback')->assertSessionHasErrors('login');
    expect(auth()->check())->toBeFalse()
        ->and($parAdresse->fresh()->google_id)->toBeNull();
});

it('refuse la fin d inscription sans passage par Google', function () {
    $this->post('/auth/google/inscription', ['us_login' => 'intrus', 'us_licence' => '1'])->assertForbidden();
});

it('laisse la connexion par mot de passe a un compte cree par Google', function () {
    User::factory()->create(['login' => 'viagoogle', 'google_id' => 'g-1', 'password' => Hash::make('choisi-plus-tard')]);

    $this->post('/ubaction__user_open', ['login' => 'viagoogle', 'pass' => 'choisi-plus-tard'])
        ->assertRedirect(route('espace'));
});

it('connecte un visiteur deja inscrit par Google au lieu de proposer un book', function () {
    $visiteur = \App\Models\Visitor::factory()->create(['google_id' => 'g-vis']);
    retourGoogle('g-vis', $visiteur->email);

    $this->get('/auth/google/callback')->assertRedirect(lien('memobook'));

    expect(auth('visitor')->id())->toBe($visiteur->id)
        ->and(auth('web')->check())->toBeFalse();
});
