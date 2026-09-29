<?php

use App\Mail\BienvenueVisiteur;
use App\Models\BookSetting;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\User;
use App\Models\Visitor;
use App\Services\Captcha\Captcha;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/*
 | Compte visiteur ouvert (ou retrouve) en ecrivant a un creatif depuis
 | la visionneuse du portail : case « Créer mon compte », mot de passe.
 */

beforeEach(function () {
    Mail::fake();
    RateLimiter::clear('demande:127.0.0.1');
    RateLimiter::clear('contact-compte|claire@example.com|127.0.0.1');

    $categorie = Category::create(['slug' => 'illustrateur', 'name' => 'Illustrateur']);

    $this->creatif = User::create([
        'login' => 'nolwenn',
        'email' => 'nolwenn@example.test',
        'password' => 'secret',
        'category_id' => $categorie->id,
        'firstname' => 'Nolwenn',
        'lastname' => 'Créatif',
        'in_home_selection' => true,
    ]);

    BookSetting::create(['user_id' => $this->creatif->id, 'diffuse_web' => true, 'diffuse_ub' => true]);
});

function url_contact(): string
{
    return 'https://'.config('ubdf.book_domain').'/intermediate_send';
}

/** @return array<string, mixed> */
function demande_compte(array $remplace = []): array
{
    return array_merge([
        'action' => 'work_A_contact',
        'us_dir' => 'nolwenn',
        'us_key' => User::where('login', 'nolwenn')->first()->publicKey(),
        'us_message' => 'Bonjour, je cherche un illustrateur pour une couverture.',
        'us_nom_prenom' => 'Claire Dupont',
        'us_mail' => 'claire@example.com',
        'captcha_answer' => app(Captcha::class)->generer('contact'),
    ], $remplace);
}

it('envoie toujours sans compte quand la case n\'est pas cochee', function () {
    $this->postJson(url_contact(), demande_compte())->assertOk()->assertJson(['error' => false, 'connecte' => false]);

    expect(Visitor::count())->toBe(0)
        ->and(Conversation::count())->toBe(1);
    $this->assertGuest('visitor');
});

it('exige un mot de passe quand la case est cochee', function () {
    $this->postJson(url_contact(), demande_compte(['compte' => '1']))
        ->assertOk()->assertJson(['error' => true])->assertJsonStructure(['error_list' => ['password']]);

    expect(Conversation::count())->toBe(0);
});

it('cree le compte visiteur, le connecte et envoie le message', function () {
    $this->postJson(url_contact(), demande_compte(['compte' => '1', 'password' => 'motdepasse1']))
        ->assertOk()->assertJson(['error' => false, 'connecte' => true]);

    $visiteur = Visitor::where('email', 'claire@example.com')->first();

    expect($visiteur)->not->toBeNull()
        ->and($visiteur->firstname)->toBe('Claire')
        ->and($visiteur->lastname)->toBe('Dupont')
        ->and(Conversation::first()->sender_email)->toBe('claire@example.com');
    $this->assertAuthenticatedAs($visiteur, 'visitor');
    Mail::assertSent(BienvenueVisiteur::class);
});

it('connecte un visiteur existant avec son mot de passe, sans recreer de compte', function () {
    $visiteur = Visitor::create(['email' => 'claire@example.com', 'password' => 'motdepasse1', 'brand' => 'ub']);

    $this->postJson(url_contact(), demande_compte(['compte' => '1', 'password' => 'motdepasse1']))
        ->assertOk()->assertJson(['error' => false, 'connecte' => true]);

    expect(Visitor::count())->toBe(1);
    $this->assertAuthenticatedAs($visiteur, 'visitor');
    Mail::assertNotSent(BienvenueVisiteur::class);
});

it('refuse un mauvais mot de passe sur une adresse existante et n\'envoie rien', function () {
    Visitor::create(['email' => 'claire@example.com', 'password' => 'motdepasse1', 'brand' => 'ub']);

    $this->postJson(url_contact(), demande_compte(['compte' => '1', 'password' => 'mauvais-mdp']))
        ->assertOk()->assertJson(['error' => true])->assertJsonStructure(['error_list' => ['password']]);

    expect(Conversation::count())->toBe(0);
    $this->assertGuest('visitor');
});

it('connecte un creatif sur son compte creatif quand l\'adresse est la sienne', function () {
    $autre = User::create([
        'login' => 'claire', 'email' => 'claire@example.com', 'password' => 'motdepasse1',
        'category_id' => $this->creatif->category_id,
    ]);

    $this->postJson(url_contact(), demande_compte(['compte' => '1', 'password' => 'motdepasse1']))
        ->assertOk()->assertJson(['error' => false, 'connecte' => true]);

    expect(Visitor::count())->toBe(0);
    $this->assertAuthenticatedAs($autre, 'web');
});

it('reprend l\'identite du visiteur connecte, sans captcha', function () {
    $visiteur = Visitor::create([
        'email' => 'claire@example.com', 'password' => 'motdepasse1', 'brand' => 'ub',
        'firstname' => 'Claire', 'lastname' => 'Dupont',
    ]);

    $this->actingAs($visiteur, 'visitor')
        ->postJson(url_contact(), demande_compte([
            'us_mail' => 'autre@example.com', 'us_nom_prenom' => '', 'captcha_answer' => '',
        ]))
        ->assertOk()->assertJson(['error' => false]);

    expect(Conversation::first()->sender_email)->toBe('claire@example.com');
});

it('affiche Mes messages au visiteur, sans attendre la confirmation', function () {
    $this->postJson(url_contact(), demande_compte(['compte' => '1', 'password' => 'motdepasse1']))->assertOk();

    $this->get('https://'.config('ubdf.book_domain').'/visiteur/messages')
        ->assertOk()->assertSee('Mes messages')->assertSee('Nolwenn Créatif');
});

it('repond au createur depuis Mes messages', function () {
    $this->postJson(url_contact(), demande_compte(['compte' => '1', 'password' => 'motdepasse1']))->assertOk();
    $fil = Conversation::first();

    Livewire\Livewire::actingAs(Visitor::first(), 'visitor')
        ->test(App\Livewire\Visiteur\Messages::class)
        ->call('ouvrir', $fil->id)
        ->set('reponse', 'Merci pour votre retour rapide.')
        ->call('repondre')
        ->assertHasNoErrors();

    expect($fil->messages()->latest('id')->first())
        ->body->toBe('Merci pour votre retour rapide.')
        ->from_owner->toBeFalse();
});

it('cache l historique anterieur au compte tant que l adresse n est pas confirmee', function () {
    $this->postJson(url_contact(), demande_compte())->assertOk();
    Conversation::query()->update(['created_at' => now()->subDay()]);
    $visiteur = Visitor::create(['email' => 'claire@example.com', 'password' => 'motdepasse1', 'brand' => 'ub']);

    Livewire\Livewire::actingAs($visiteur, 'visitor')
        ->test(App\Livewire\Visiteur\Messages::class)
        ->assertSee('Aucun message pour le moment.');
});

it('ouvre le message choisi sur le tableau de bord, deplie et lu', function () {
    $this->postJson(url_contact(), demande_compte(['compte' => '1', 'password' => 'motdepasse1']))->assertOk();
    $fil = Conversation::first();
    $fil->messages()->create(['from_owner' => true, 'body' => 'Avec plaisir, parlons-en.']);

    $this->get('https://'.config('ubdf.book_domain').'/visiteur')
        ->assertOk()->assertSee('/visiteur/messages?fil='.$fil->id, false);

    $this->get('https://'.config('ubdf.book_domain').'/visiteur/messages?fil='.$fil->id)
        ->assertOk()->assertSee('Avec plaisir, parlons-en.');

    expect($fil->messages()->where('from_owner', true)->whereNull('read_at')->count())->toBe(0);
});

it('regroupe les visites par mois, 30 a la fois, puis charge les anterieures', function () {
    $visiteur = Visitor::create(['email' => 'claire@example.com', 'password' => 'motdepasse1', 'brand' => 'ub']);

    foreach (range(1, 31) as $i) {
        $book = User::create(['login' => 'book'.$i, 'email' => "book{$i}@example.test", 'password' => 'x',
            'category_id' => $this->creatif->category_id, 'firstname' => 'Book', 'lastname' => 'N'.$i]);
        $visiteur->visites()->attach($book->id, ['visited_at' => now()->startOfMonth()->subMonths($i > 20 ? 1 : 0)->addHours($i)]);
    }

    Livewire\Livewire::actingAs($visiteur, 'visitor')
        ->test(App\Livewire\Visiteur\DernieresVisites::class)
        ->assertSee(now()->translatedFormat('F Y'))
        ->assertSee('Charger plus')
        ->assertDontSee('Book N21<', false)
        ->call('chargerPlus')
        ->assertSee('Book N21')
        ->assertDontSee('Charger plus');
});

it('inscrit le nouveau compte visiteur a la newsletter par defaut', function () {
    $this->postJson(url_contact(), demande_compte(['compte' => '1', 'password' => 'motdepasse1']))->assertOk();

    expect(App\Models\NewsletterMail::where('email', 'claire@example.com')->exists())->toBeTrue();
});

it('compose les initiales de l avatar visiteur', function () {
    $v = new Visitor(['email' => 'claire@example.com']);
    expect($v->initiales())->toBe('CL');

    $v->firstname = 'élodie';
    expect($v->initiales())->toBe('ÉL');

    $v->lastname = 'Dupont';
    expect($v->initiales())->toBe('ÉD');
});

it('affiche l avatar du visiteur dans le menu du haut et sur Mon compte', function () {
    $visiteur = Visitor::create(['email' => 'claire@example.com', 'password' => 'motdepasse1', 'brand' => 'ub',
        'firstname' => 'Claire', 'lastname' => 'Dupont']);

    $this->actingAs($visiteur, 'visitor')->get('https://'.config('ubdf.book_domain').'/visiteur/compte')
        ->assertOk()->assertSee('>CD</span>', false);
    $this->get('https://'.config('ubdf.book_domain').'/')->assertOk()->assertSee('>CD</span>', false);
});
