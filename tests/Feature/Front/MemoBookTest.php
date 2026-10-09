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
        ->assertOk()->assertSee('mémoBook')->assertSee('Le Gall')->assertSee('Martin');

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

it('ramene le visiteur sur le book qu il regardait', function () {
    Visitor::factory()->create(['email' => 'lea@example.com', 'password' => Hash::make('secret-2026')]);

    $this->post('/ubaction__user_open', ['login' => 'lea@example.com', 'pass' => 'secret-2026', 'retour' => '/#nolwenn'])
        ->assertRedirect('/#nolwenn');
});

it('ignore un retour vers un autre site', function (string $retour) {
    Visitor::factory()->create(['email' => 'lea@example.com', 'password' => Hash::make('secret-2026')]);

    $this->post('/ubaction__user_open', ['login' => 'lea@example.com', 'pass' => 'secret-2026', 'retour' => $retour])
        ->assertRedirect(route('visiteur.tableau'));
})->with(['https://evil.example', '//evil.example', '/\\evil.example']);

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

    $this->get('/memobook/messages/'.$fil->id)->assertRedirect();

    // Non confirmee : le fil anterieur a l'ouverture du compte se cache.
    $fil->forceFill(['created_at' => $visiteur->created_at->subDay()])->save();
    $visiteur->forceFill(['email_verified_at' => null])->save();
    $this->get('/memobook/messages/'.$fil->id)->assertNotFound();
});

it('ne montre pas le fil d un autre', function () {
    $fil = Conversation::create([
        'user_id' => $this->book->id, 'channel' => 'portail', 'subject' => 'contact',
        'sender_name' => 'Autre', 'sender_email' => 'autre@example.com',
        'selector' => str_repeat('b', 24), 'last_message_at' => now(),
    ]);

    $this->actingAs(Visitor::factory()->create(), 'visitor')
        ->get('/memobook/messages/'.$fil->id)->assertNotFound();
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

it('compte les messages echanges avec un book et les montre dans la fenetre', function () {
    $visiteur = Visitor::factory()->create(['email' => 'lea@example.com']);
    MemoBook::create(['visitor_id' => $visiteur->id, 'book_id' => $this->book->id]);
    MemoBook::create(['visitor_id' => $visiteur->id, 'book_id' => $this->autre->id]);

    $fil = Conversation::create([
        'user_id' => $this->book->id, 'channel' => 'portail', 'subject' => 'contact',
        'sender_name' => 'Léa', 'sender_email' => 'lea@example.com',
        'selector' => str_repeat('c', 24), 'last_message_at' => now(),
    ]);
    $fil->messages()->createMany([
        ['from_owner' => false, 'body' => 'Bonjour Nolwenn'],
        ['from_owner' => true, 'body' => 'Bonjour Léa, avec plaisir'],
        ['from_owner' => false, 'body' => 'Merci !'],
    ]);

    expect(app(App\Services\Memo\MemoBooks::class)->compteursMessages($visiteur, [$this->book->id, $this->autre->id]))
        ->toBe([$this->book->id => ['recus' => 1, 'envoyes' => 2]]);

    Livewire\Livewire::actingAs($visiteur, 'visitor')
        ->test(App\Livewire\Memo\Liste::class)
        ->assertSee('1 reçu')->assertSee('2 envoyés')
        ->call('ouvrirMessages', 'nolwenn')
        ->assertSee('Messages échangés avec')->assertSee('Bonjour Léa, avec plaisir')
        ->call('fermerMessages')
        ->assertDontSee('Bonjour Léa, avec plaisir');

    expect($fil->messages()->where('from_owner', true)->whereNull('read_at')->count())->toBe(0);
});

it('range les books par annee et par mois de memorisation', function () {
    $visiteur = Visitor::factory()->create();
    MemoBook::create(['visitor_id' => $visiteur->id, 'book_id' => $this->book->id, 'created_at' => '2026-09-10']);
    MemoBook::create(['visitor_id' => $visiteur->id, 'book_id' => $this->autre->id, 'created_at' => '2025-03-02']);

    Livewire\Livewire::actingAs($visiteur, 'visitor')
        ->test(App\Livewire\Memo\Liste::class)
        ->assertSeeInOrder(['2026', 'septembre', 'Le Gall', '2025', 'mars', 'Martin']);
});

it('ajoute les codes QR au pdf sur demande', function () {
    $visiteur = Visitor::factory()->create();
    MemoBook::create(['visitor_id' => $visiteur->id, 'book_id' => $this->book->id]);

    $sans = $this->actingAs($visiteur, 'visitor')->get('/memobook/pdf')->assertOk()->getContent();
    $avec = $this->get('/memobook/pdf?qr=1')->assertOk()->getContent();

    expect(strlen($avec))->toBeGreaterThan(strlen($sans));
});

it('partage publiquement le memo en lecture seule', function () {
    $visiteur = Visitor::factory()->create(['email' => 'lea@example.com']);
    MemoBook::create(['visitor_id' => $visiteur->id, 'book_id' => $this->book->id]);
    $fil = Conversation::create([
        'user_id' => $this->book->id, 'channel' => 'portail', 'subject' => 'contact',
        'sender_name' => 'Léa', 'sender_email' => 'lea@example.com',
        'selector' => str_repeat('d', 24), 'last_message_at' => now(),
    ]);
    $fil->messages()->create(['from_owner' => true, 'body' => 'Réponse privée']);

    Livewire\Livewire::actingAs($visiteur, 'visitor')
        ->test(App\Livewire\Memo\Liste::class)
        ->assertDontSee('Afficher le QR code')
        ->call('basculerPartage')
        ->assertSee('Afficher le QR code');

    $partage = App\Models\MemoPartage::firstWhere('visitor_id', $visiteur->id);
    expect($partage->url())->toMatch('#/memobook/[A-Za-z0-9]{10}$#');
    auth('visitor')->logout();

    $this->get($partage->url())->assertOk()
        ->assertSee('Le Gall')->assertDontSee('Partagé par')->assertDontSee('lea@example.com')->assertDontSee('1 reçu')->assertDontSee('Retirer')->assertDontSee('Réponse privée');

    Livewire\Livewire::actingAs($visiteur, 'visitor')->test(App\Livewire\Memo\Liste::class)->call('basculerPartage');
    auth('visitor')->logout();

    $this->get($partage->url())->assertNotFound();
});

it('montre sur la page publique le createur qui partage', function () {
    $creatif = User::factory()->create(['firstname' => 'Adolie', 'lastname' => 'Day']);
    MemoBook::create(['user_id' => $creatif->id, 'book_id' => $this->book->id]);
    $partage = app(App\Services\Memo\MemoBooks::class)->basculerPartage($creatif);

    $this->get($partage->url())->assertOk()->assertSee('Partagé par')->assertSee('Adolie Day');
});

it('envoie un message a un book du memo', function () {
    $visiteur = Visitor::factory()->create(['email' => 'lea@example.com']);
    MemoBook::create(['visitor_id' => $visiteur->id, 'book_id' => $this->book->id]);

    Livewire\Livewire::actingAs($visiteur, 'visitor')
        ->test(App\Livewire\Memo\Liste::class)
        ->call('ouvrirEcriture', 'nolwenn')
        ->assertSee('Envoyer un message à')
        ->set('nomExpediteur', 'Léa Martin')
        ->set('messageTexte', 'court')
        ->call('envoyerMessage')
        ->assertHasErrors('messageTexte')
        ->set('messageTexte', 'Bonjour, votre travail me plaît beaucoup.')
        ->call('envoyerMessage')
        ->assertHasNoErrors()
        ->assertSee('Message envoyé');

    $fil = Conversation::firstWhere('user_id', $this->book->id);
    expect($fil->sender_email)->toBe('lea@example.com')
        ->and($fil->sender_name)->toBe('Léa Martin')
        ->and($fil->messages()->first()->body)->toBe('Bonjour, votre travail me plaît beaucoup.');

    Mail::assertSent(App\Mail\DemandeRecue::class);
});

it('exporte en pdf la version publique d un memo partage', function () {
    $creatif = User::factory()->create();
    MemoBook::create(['user_id' => $creatif->id, 'book_id' => $this->book->id]);
    $partage = app(App\Services\Memo\MemoBooks::class)->basculerPartage($creatif);

    $this->get($partage->url())->assertSee('Exporter en PDF');
    $this->get($partage->url().'/pdf?qr=1')->assertOk()->assertHeader('Content-Type', 'application/pdf');

    app(App\Services\Memo\MemoBooks::class)->basculerPartage($creatif);
    $this->get($partage->url().'/pdf')->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Mon compte (visiteur)
|--------------------------------------------------------------------------
*/

it('affiche la page mon compte du visiteur', function () {
    $visiteur = Visitor::factory()->create(['email' => 'lea@example.com']);

    $this->actingAs($visiteur, 'visitor')->get('/visiteur/compte')
        ->assertOk()->assertSee('Mes informations')->assertSee('lea@example.com')->assertSee('Supprimer mon compte');
});

it('modifie nom, prenom et mot de passe du visiteur', function () {
    $visiteur = Visitor::factory()->create();

    Livewire\Livewire::actingAs($visiteur, 'visitor')
        ->test(App\Livewire\Visiteur\Compte::class)
        ->call('enregistrerChamp', 'firstname', 'Léa')->assertReturned(['ok' => true])
        ->call('enregistrerChamp', 'lastname', 'Martin')
        ->call('enregistrerChamp', 'motDePasse', 'court')->assertReturned(fn ($r) => isset($r['erreur']))
        ->call('enregistrerChamp', 'motDePasse', 'nouveau-2026');

    $visiteur->refresh();
    expect($visiteur->fullName())->toBe('Léa Martin')
        ->and(Hash::check('nouveau-2026', $visiteur->password))->toBeTrue();
});

it('change l adresse du visiteur et la repasse non confirmee', function () {
    $visiteur = Visitor::factory()->create(['email' => 'lea@example.com']);
    App\Models\NewsletterMail::create(['email' => 'lea@example.com']);

    Livewire\Livewire::actingAs($visiteur, 'visitor')
        ->test(App\Livewire\Visiteur\Compte::class)
        ->call('enregistrerChamp', 'email', $this->book->email)->assertReturned(fn ($r) => isset($r['erreur']))
        ->call('enregistrerChamp', 'email', 'Lea.Nouvelle@example.com')->assertReturned(['ok' => true]);

    $visiteur->refresh();
    expect($visiteur->email)->toBe('lea.nouvelle@example.com')
        ->and($visiteur->email_verified_at)->toBeNull()
        ->and(App\Models\NewsletterMail::where('email', 'lea.nouvelle@example.com')->exists())->toBeTrue();
    Mail::assertSent(BienvenueVisiteur::class, fn ($m) => $m->hasTo('lea.nouvelle@example.com'));
});

it('inscrit et desinscrit le visiteur de la newsletter', function () {
    $visiteur = Visitor::factory()->create(['email' => 'lea@example.com']);

    $composant = Livewire\Livewire::actingAs($visiteur, 'visitor')->test(App\Livewire\Visiteur\Compte::class)
        ->assertSet('newsletter', false)->set('newsletter', true);
    expect(App\Models\NewsletterMail::where('email', 'lea@example.com')->exists())->toBeTrue();

    $composant->set('newsletter', false);
    expect(App\Models\NewsletterMail::where('email', 'lea@example.com')->exists())->toBeFalse();
});

it('supprime le compte visiteur apres le mot de passe actuel', function () {
    $visiteur = Visitor::factory()->create(['password' => Hash::make('secret-2026')]);
    MemoBook::create(['visitor_id' => $visiteur->id, 'book_id' => $this->book->id]);

    Livewire\Livewire::actingAs($visiteur, 'visitor')->test(App\Livewire\Visiteur\Compte::class)
        ->set('motDePasseActuel', 'faux')->call('supprimerCompte')->assertHasErrors('motDePasseActuel')
        ->set('motDePasseActuel', 'secret-2026')->call('supprimerCompte')->assertRedirect(route('home'));

    expect(Visitor::withTrashed()->find($visiteur->id))->toBeNull()
        ->and(MemoBook::where('visitor_id', $visiteur->id)->count())->toBe(0);
});

it('ne prend pas la couverture du memo dans un portfolio protege', function () {
    $prive = $this->book->galleries()->create(['name' => 'Privé', 'status' => 'published', 'password' => 'secret42']);
    $prive->media()->create(['user_id' => $this->book->id, 'filename' => 'prive.jpg', 'status' => 'published', 'position' => 0]);
    $libre = $this->book->galleries()->create(['name' => 'Libre', 'status' => 'published']);
    $libre->media()->create(['user_id' => $this->book->id, 'filename' => 'libre.jpg', 'status' => 'published', 'position' => 1]);

    $visiteur = Visitor::factory()->create();
    MemoBook::create(['visitor_id' => $visiteur->id, 'book_id' => $this->book->id]);

    // La couverture du PDF est le premier visuel charge par MemoBooks.
    $book = app(App\Services\Memo\MemoBooks::class)->books($visiteur)->first();

    expect($book->media->pluck('filename')->all())->toBe(['libre.jpg']);
});
