<?php

use App\Mail\BienvenueVisiteur;
use App\Models\Conversation;
use App\Models\MemoBook;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    Mail::fake();
    RateLimiter::clear('inscription-visiteur|127.0.0.1');
    RateLimiter::clear('connexion|nolwenn@example.com|127.0.0.1');
    RateLimiter::clear('connexion|lea@example.com|127.0.0.1');

    $this->book = User::factory()->create(['login' => 'nolwenn', 'firstname' => 'Nolwenn', 'lastname' => 'Le Gall']);
    $this->autre = User::factory()->create(['login' => 'arthur', 'firstname' => 'Arthur', 'lastname' => 'Martin']);
});

/*
|--------------------------------------------------------------------------
| Inscription visiteur depuis le coeur
|--------------------------------------------------------------------------
*/

it('cree un compte visiteur, le connecte et y verse la selection du navigateur', function () {
    $this->postJson('/memo/compte', [
        'email' => 'Lea@Example.com',
        'password' => 'secret-2026',
        'logins' => ['nolwenn', 'inconnu'],
    ])->assertOk()->assertJson(['url' => route('memobook')]);

    $visiteur = Visitor::firstWhere('email', 'lea@example.com');

    expect($visiteur)->not->toBeNull()
        ->and(auth('visitor')->id())->toBe($visiteur->id)
        ->and(auth('web')->check())->toBeFalse()
        ->and($visiteur->memoBooks()->pluck('book_id')->all())->toBe([$this->book->id]);

    Mail::assertSent(BienvenueVisiteur::class);
});

it('refuse une adresse deja prise par un visiteur ou un creatif', function () {
    Visitor::factory()->create(['email' => 'lea@example.com']);

    $this->postJson('/memo/compte', ['email' => 'lea@example.com', 'password' => 'secret-2026'])
        ->assertUnprocessable()->assertJsonValidationErrors('email');

    $this->postJson('/memo/compte', ['email' => $this->book->email, 'password' => 'secret-2026'])
        ->assertUnprocessable()->assertJsonValidationErrors('email');
});

it('confirme l adresse du visiteur par lien signe', function () {
    $visiteur = Visitor::factory()->unverified()->create();
    $lien = app(App\Actions\Visiteur\CreerCompteVisiteur::class)->lienConfirmation($visiteur);

    $this->get($lien)->assertRedirect();

    expect($visiteur->fresh()->email_verified_at)->not->toBeNull();
});

/*
|--------------------------------------------------------------------------
| API du memo
|--------------------------------------------------------------------------
*/

it('ajoute et retire un book du memo d un visiteur, sans doublon', function () {
    $visiteur = Visitor::factory()->create();

    $this->actingAs($visiteur, 'visitor');
    $this->postJson('/memo/ajouter', ['login' => 'nolwenn'])->assertOk()->assertJson(['logins' => ['nolwenn']]);
    $this->postJson('/memo/ajouter', ['login' => 'nolwenn'])->assertOk();
    $this->postJson('/memo/ajouter', ['login' => 'arthur'])->assertOk();

    expect(MemoBook::where('visitor_id', $visiteur->id)->count())->toBe(2);

    $this->postJson('/memo/retirer', ['login' => 'nolwenn'])->assertOk()->assertJson(['logins' => ['arthur']]);
});

it('tient aussi le memo d un creatif connecte', function () {
    $creatif = User::factory()->create();

    $this->actingAs($creatif);
    $this->postJson('/memo/ajouter', ['login' => 'nolwenn'])->assertOk();

    expect($creatif->memoBooks()->pluck('book_id')->all())->toBe([$this->book->id]);
});

it('refuse l api du memo a un anonyme', function () {
    $this->postJson('/memo/ajouter', ['login' => 'nolwenn'])->assertUnauthorized();
});

it('rend 404 pour un book inconnu', function () {
    $this->actingAs(Visitor::factory()->create(), 'visitor');

    $this->postJson('/memo/ajouter', ['login' => 'personne'])->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Page du memo
|--------------------------------------------------------------------------
*/

it('affiche le memo d un visiteur et filtre par nom', function () {
    $visiteur = Visitor::factory()->create();
    MemoBook::create(['visitor_id' => $visiteur->id, 'book_id' => $this->book->id]);
    MemoBook::create(['visitor_id' => $visiteur->id, 'book_id' => $this->autre->id]);

    $this->actingAs($visiteur, 'visitor')->get('/memobook')
        ->assertOk()->assertSee('Mon mémo book')->assertSee('Le Gall')->assertSee('Martin');

    Livewire\Livewire::actingAs($visiteur, 'visitor')
        ->test(App\Livewire\Memo\Liste::class)
        ->set('recherche', 'nolwenn le gall')
        ->assertSee('Le Gall')->assertDontSee('Arthur')
        ->call('retirer', 'nolwenn')
        ->assertDispatched('memo-change', total: 1);

    expect($visiteur->memoBooks()->count())->toBe(1);
});

it('affiche le memo d un creatif dans son espace', function () {
    $creatif = User::factory()->create();
    MemoBook::create(['user_id' => $creatif->id, 'book_id' => $this->book->id]);

    $this->actingAs($creatif)->get('/memobook')->assertOk()->assertSee('Le Gall');
});

it('renvoie un anonyme vers la connexion', function () {
    $this->get('/memobook')->assertRedirect(route('accueil'));
});

it('exporte le memo en pdf', function () {
    $visiteur = Visitor::factory()->create();
    MemoBook::create(['visitor_id' => $visiteur->id, 'book_id' => $this->book->id]);

    $this->actingAs($visiteur, 'visitor')->get('/memobook/pdf')
        ->assertOk()->assertHeader('Content-Type', 'application/pdf');
});

/*
|--------------------------------------------------------------------------
| Connexion unifiee
|--------------------------------------------------------------------------
*/

it('connecte un visiteur par son adresse et l amene sur son tableau de bord', function () {
    $visiteur = Visitor::factory()->create(['email' => 'lea@example.com', 'password' => Hash::make('secret-2026')]);

    $this->post('/ubaction__user_open', ['login' => 'lea@example.com', 'pass' => 'secret-2026'])
        ->assertRedirect(route('visiteur.tableau'));

    expect(auth('visitor')->id())->toBe($visiteur->id)->and(auth('web')->check())->toBeFalse();
});

it('connecte un creatif par son adresse', function () {
    $this->book->update(['email' => 'nolwenn@example.com', 'password' => Hash::make('secret-2026')]);

    $this->post('/ubaction__user_open', ['login' => 'nolwenn@example.com', 'pass' => 'secret-2026'])
        ->assertRedirect(route('espace'));

    expect(auth('web')->id())->toBe($this->book->id);
});

it('departage deux books de la meme adresse par le mot de passe', function () {
    $this->book->update(['email' => 'nolwenn@example.com', 'password' => Hash::make('secret-2026')]);
    $this->autre->update(['email' => 'nolwenn@example.com', 'password' => Hash::make('autre-2026')]);

    $this->post('/ubaction__user_open', ['login' => 'nolwenn@example.com', 'pass' => 'autre-2026'])
        ->assertRedirect(route('espace'));

    expect(auth('web')->id())->toBe($this->autre->id);
});

it('demande l identifiant si l adresse et le mot de passe designent plusieurs books', function () {
    $this->book->update(['email' => 'nolwenn@example.com', 'password' => Hash::make('secret-2026')]);
    $this->autre->update(['email' => 'nolwenn@example.com', 'password' => Hash::make('secret-2026')]);

    $this->from('/')->post('/ubaction__user_open', ['login' => 'nolwenn@example.com', 'pass' => 'secret-2026'])
        ->assertSessionHasErrors(['login' => 'Plusieurs books utilisent cette adresse : connectez-vous avec votre identifiant.']);

    expect(auth('web')->check())->toBeFalse();
});

it('ferme la session visiteur quand un creatif se connecte', function () {
    $this->actingAs(Visitor::factory()->create(), 'visitor');
    $this->book->update(['password' => Hash::make('secret-2026')]);

    $this->post('/ubaction__user_open', ['login' => 'nolwenn', 'pass' => 'secret-2026']);

    expect(auth('web')->id())->toBe($this->book->id)->and(auth('visitor')->check())->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Espace du visiteur
|--------------------------------------------------------------------------
*/

it('interdit l espace creatif a un visiteur', function () {
    $this->actingAs(Visitor::factory()->create(), 'visitor');

    $this->get('/espace')->assertRedirect(route('accueil'));
});

it('montre au visiteur ses messages une fois son adresse confirmee', function () {
    $visiteur = Visitor::factory()->create(['email' => 'lea@example.com']);
    $fil = Conversation::create([
        'user_id' => $this->book->id, 'channel' => 'portail', 'subject' => 'contact',
        'sender_name' => 'Léa', 'sender_email' => 'lea@example.com',
        'selector' => str_repeat('a', 24), 'last_message_at' => now(),
    ]);

    $this->actingAs($visiteur, 'visitor')->get('/visiteur')
        ->assertOk()->assertSee('Mes derniers messages')->assertSee('Le Gall');

    $this->get('/visiteur/messages/'.$fil->id)->assertRedirect();

    $visiteur->forceFill(['email_verified_at' => null])->save();
    $this->get('/visiteur/messages/'.$fil->id)->assertNotFound();
});

it('ne montre pas le fil d un autre', function () {
    $fil = Conversation::create([
        'user_id' => $this->book->id, 'channel' => 'portail', 'subject' => 'contact',
        'sender_name' => 'Autre', 'sender_email' => 'autre@example.com',
        'selector' => str_repeat('b', 24), 'last_message_at' => now(),
    ]);

    $this->actingAs(Visitor::factory()->create(), 'visitor')
        ->get('/visiteur/messages/'.$fil->id)->assertNotFound();
});

it('note les visites de books d un visiteur connecte seulement', function () {
    $this->get('/ubvisite/nolwenn.gif')->assertOk();
    expect(DB::table('visitor_book_visits')->count())->toBe(0);

    $visiteur = Visitor::factory()->create();
    $this->actingAs($visiteur, 'visitor')->get('/ubvisite/nolwenn.gif')->assertOk();
    $this->get('/ubvisite/nolwenn.gif')->assertOk();

    expect(DB::table('visitor_book_visits')->where('visitor_id', $visiteur->id)->count())->toBe(1);
    $this->get('/visiteur')->assertSee('Mes dernières visites')->assertSee('Le Gall');
});

it('reinitialise le mot de passe d un visiteur', function () {
    $visiteur = Visitor::factory()->create(['email' => 'lea@example.com']);
    $jeton = Password::broker('visitors')->createToken($visiteur);

    $this->post('/visiteur/mot-de-passe/'.$jeton.'?email=lea@example.com', [
        'password' => 'nouveau-2026', 'password_confirmation' => 'nouveau-2026',
    ])->assertRedirect(route('visiteur.tableau'));

    expect(Hash::check('nouveau-2026', $visiteur->fresh()->password))->toBeTrue();
});

it('passe au portail le memo d un visiteur connecte', function () {
    $visiteur = Visitor::factory()->create();
    MemoBook::create(['visitor_id' => $visiteur->id, 'book_id' => $this->book->id]);

    $this->get('/')->assertOk()
        ->assertSee('"memo":{"connecte":false,"visiteur":false,"logins":[]}', false)
        ->assertSee('data-modale="memo-compte"', false);

    $this->actingAs($visiteur, 'visitor')->get('/')->assertOk()
        ->assertSee('"memo":{"connecte":true,"visiteur":true,"logins":["nolwenn"]}', false)
        ->assertDontSee('data-modale="memo-compte"', false);
});
