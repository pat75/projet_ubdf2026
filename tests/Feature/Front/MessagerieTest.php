<?php

use App\Mail\DemandeRecue;
use App\Mail\DemandeTransmise;
use App\Mail\ReponseRecue;
use App\Models\BookSetting;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Media;
use App\Models\User;
use App\Services\Messagerie\Intermediation;
use App\Support\Captcha;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    Mail::fake();
    RateLimiter::clear('demande:127.0.0.1');

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

    BookSetting::create([
        'user_id' => $this->creatif->id,
        'diffuse_web' => true,
        'diffuse_ub' => true,
    ]);

    Media::create([
        'user_id' => $this->creatif->id,
        'filename' => 'visuel.jpg',
        'title' => 'Visuel',
        'status' => 'published',
    ]);
});

function portail_url(string $path): string
{
    return 'https://'.config('ubdf.book_domain').$path;
}

/** @return array<string, mixed> */
function demande(array $remplace = []): array
{
    return array_merge([
        'action' => 'work_A_contact',
        'us_dir' => 'nolwenn',
        'us_key' => User::where('login', 'nolwenn')->first()->publicKey(),
        'us_message' => 'Bonjour, je cherche un illustrateur pour une couverture.',
        'us_nom_prenom' => 'Claire Dupont',
        'us_mail' => 'claire@example.com',
        'captcha_answer' => Captcha::generer(),
    ], $remplace);
}

it('enregistre une demande et notifie les deux parties', function () {
    $reponse = $this->postJson(portail_url('/intermediate_send'), demande());

    $reponse->assertOk()->assertJson([
        'error' => false,
        'action' => 'work_contact',
        'savedb_result' => true,
    ]);

    $conversation = Conversation::first();

    expect($conversation->sender_email)->toBe('claire@example.com')
        ->and($conversation->user_id)->toBe($this->creatif->id)
        ->and($conversation->messages)->toHaveCount(1)
        ->and($conversation->messages->first()->from_owner)->toBeFalse();

    Mail::assertSent(DemandeRecue::class);
    Mail::assertSent(DemandeTransmise::class);
});

it('donne a chaque partie un jeton different', function () {
    $this->postJson(portail_url('/intermediate_send'), demande())->assertOk();

    $conversation = Conversation::first();

    // Le legacy envoyait le meme jeton aux deux parties, le segment de
    // controle etant derive de ce jeton : chacun pouvait calculer le lien
    // de l'autre. Les deux acces sont maintenant independants.
    expect($conversation->owner_token)->not->toBe($conversation->sender_token)
        ->and($conversation->owner_token)->toHaveLength(64);
});

it('ne stocke jamais un jeton en clair', function () {
    $intermediation = app(Intermediation::class);

    $ouverture = $intermediation->ouvrir($this->creatif, demande(), '127.0.0.1');
    $jeton = str($ouverture['liens'][Intermediation::PROPRIETAIRE])->afterLast('/')->toString();

    expect($ouverture['conversation']->owner_token)->not->toBe($jeton)
        ->and($ouverture['conversation']->owner_token)->toBe(hash('sha256', $jeton));
});

it('refuse une cle publique qui ne correspond pas au login', function () {
    // us_key empeche de poster une demande a un login devine.
    $this->postJson(portail_url('/intermediate_send'), demande(['us_key' => 'faux']))
        ->assertOk()
        ->assertJson(['error' => true]);

    expect(Conversation::count())->toBe(0);
});

it('rend un 200 portant error plutot qu un 422', function () {
    // Le JavaScript du front 2018 ne branche que son gestionnaire de succes.
    $this->postJson(portail_url('/intermediate_send'), demande(['us_mail' => 'pas-une-adresse']))
        ->assertOk()
        ->assertJson(['error' => true, 'savedb_result' => false])
        ->assertJsonPath('error_list.us_mail.0', 'Cette adresse électronique est invalide.');
});

it('refuse un captcha errone', function () {
    Captcha::generer();

    $this->postJson(portail_url('/intermediate_send'), array_merge(demande(), ['captcha_answer' => 'ZZZZZ']))
        ->assertOk()
        ->assertJson(['error' => true]);

    expect(Conversation::count())->toBe(0);
});

it('invalide le captcha meme apres un echec', function () {
    // Le legacy ne retirait le code de la session qu'en cas de succes : on
    // pouvait reessayer indefiniment sur la meme image.
    $code = Captcha::generer();

    $this->postJson(portail_url('/intermediate_send'), demande(['captcha_answer' => 'ZZZZZ']));

    $this->postJson(portail_url('/intermediate_send'), demande(['captcha_answer' => $code]))
        ->assertOk()
        ->assertJson(['error' => true]);
});

it('enregistre un message suspect sans le relayer par courriel', function () {
    // Un faux positif ne doit jamais faire disparaitre une commande.
    $this->postJson(portail_url('/intermediate_send'), demande([
        'us_message' => 'Bonjour, PlayAmo Casino vous offre un bonus exceptionnel.',
    ]))->assertOk()->assertJson(['error' => false]);

    expect(Conversation::first()->is_spam)->toBeTrue();

    Mail::assertNothingSent();
});

it('limite le nombre de demandes par adresse IP', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson(portail_url('/intermediate_send'), demande([
            'us_mail' => "visiteur{$i}@example.com",
        ]))->assertJson(['error' => false]);
    }

    $this->postJson(portail_url('/intermediate_send'), demande(['us_mail' => 'sixieme@example.com']))
        ->assertOk()
        ->assertJson(['error' => true]);

    expect(Conversation::count())->toBe(5);
});

it('ouvre le fil depuis le lien du creatif', function () {
    $ouverture = app(Intermediation::class)->ouvrir($this->creatif, demande(), '127.0.0.1');

    $this->get($ouverture['liens'][Intermediation::PROPRIETAIRE])
        ->assertOk()
        ->assertSee('Claire Dupont')
        ->assertSee('couverture', false)
        // L'adresse du visiteur ne doit jamais apparaitre dans la page.
        ->assertDontSee('claire@example.com');
});

it('refuse un jeton qui n ouvre pas le role demande', function () {
    $ouverture = app(Intermediation::class)->ouvrir($this->creatif, demande(), '127.0.0.1');
    $jeton = str($ouverture['liens'][Intermediation::EMETTEUR])->afterLast('/')->toString();

    // Avec le jeton du visiteur, on n'entre pas du cote du creatif.
    $this->get(portail_url("/messages/owner/{$ouverture['conversation']->selector}/{$jeton}"))
        ->assertNotFound();
});

it('refuse un lien expire', function () {
    $ouverture = app(Intermediation::class)->ouvrir($this->creatif, demande(), '127.0.0.1');

    $ouverture['conversation']->forceFill([
        'last_message_at' => now()->subDays(config('messagerie.lien_valide_jours') + 1),
    ])->save();

    $this->get($ouverture['liens'][Intermediation::EMETTEUR])->assertNotFound();
});

it('enregistre une reponse et previent l autre partie', function () {
    $ouverture = app(Intermediation::class)->ouvrir($this->creatif, demande(), '127.0.0.1');
    $lien = $ouverture['liens'][Intermediation::PROPRIETAIRE];

    $this->post($lien, ['message' => 'Bonjour, votre projet m’intéresse.'])
        ->assertRedirect($lien);

    $conversation = $ouverture['conversation']->fresh(['messages']);

    expect($conversation->messages)->toHaveCount(2)
        ->and($conversation->messages->last()->from_owner)->toBeTrue();

    Mail::assertSent(ReponseRecue::class);
});

it('renouvelle le jeton de la partie notifiee', function () {
    $intermediation = app(Intermediation::class);
    $ouverture = $intermediation->ouvrir($this->creatif, demande(), '127.0.0.1');

    $ancien = $ouverture['conversation']->sender_token;

    $this->post($ouverture['liens'][Intermediation::PROPRIETAIRE], ['message' => 'Bonjour à vous.']);

    // Le lien du visiteur a circule par courriel : chaque notification en
    // emet un neuf, et l'ancien cesse d'ouvrir le fil.
    expect($ouverture['conversation']->fresh()->sender_token)->not->toBe($ancien);

    $this->get($ouverture['liens'][Intermediation::EMETTEUR])->assertNotFound();
});

it('marque lus les messages de l autre partie, pas les siens', function () {
    $ouverture = app(Intermediation::class)->ouvrir($this->creatif, demande(), '127.0.0.1');

    $this->get($ouverture['liens'][Intermediation::PROPRIETAIRE])->assertOk();

    expect($ouverture['conversation']->messages()->first()->read_at)->not->toBeNull();
});

it('sert une image de captcha', function () {
    $this->get(portail_url('/captcha_img'))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/svg+xml');

    expect(session(Captcha::SESSION))->toHaveLength(config('messagerie.captcha.longueur'));
});
