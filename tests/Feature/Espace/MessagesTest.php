<?php

use App\Livewire\Espace\Messages;
use App\Mail\ReponseRecue;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->creatif = User::factory()->create();
    $this->actingAs($this->creatif);
    $this->conversation = $this->creatif->conversations()->create([
        'channel' => 'book', 'subject' => 'autre', 'sender_name' => 'Client Dupont',
        'sender_email' => 'client@example.test', 'selector' => str_repeat('a', 24), 'last_message_at' => now(),
    ]);
    $this->conversation->messages()->create(['from_owner' => false, 'body' => 'Bonjour, un devis ?']);
});

it('liste et ouvre les demandes, en les marquant lues', function () {
    $this->get(route('espace.messages'))->assertOk()->assertSee('Client Dupont');

    Livewire::test(Messages::class)->call('ouvrir', $this->conversation->id)->assertSee('Bonjour, un devis ?');

    expect($this->conversation->messages()->whereNull('read_at')->count())->toBe(0);
});

it('repond et previent l emetteur', function () {
    Mail::fake();

    Livewire::test(Messages::class)
        ->call('ouvrir', $this->conversation->id)
        ->set('reponse', 'Avec plaisir.')
        ->call('repondre')
        ->assertHasNoErrors()
        ->assertSee('Avec plaisir.');

    Mail::assertSent(ReponseRecue::class, fn ($m) => $m->hasTo('client@example.test')
        // Le sujet nomme l'auteur de la reponse, pas seulement l'objet de la demande.
        && str_contains($m->envelope()->subject, $this->creatif->fullName()));
});

it('masque les demandes des autres', function () {
    $autre = User::factory()->create()->conversations()->create(['channel' => 'book', 'sender_name' => 'Autre', 'selector' => str_repeat('b', 24)]);

    $this->get(route('espace.messages'))->assertDontSee('Autre');
    Livewire::test(Messages::class)->call('ouvrir', $autre->id)->assertNotFound();
});

it('signale les indesirables et les envoie tous a la poubelle d un clic', function () {
    $admin = $this->creatif->conversations()->create(['channel' => 'book', 'sender_name' => 'Spammeur', 'is_spam' => true, 'selector' => str_repeat('c', 24), 'last_message_at' => now()]);
    $ia = $this->creatif->conversations()->create(['channel' => 'book', 'sender_name' => 'Robot', 'selector' => str_repeat('e', 24), 'last_message_at' => now()]);
    $ia->forceFill(['spam_ia' => true])->save();

    $this->get(route('espace.messages'))->assertOk()
        ->assertSee('Spammeur')->assertSee('Indésirable')
        ->assertSee('Supprimer les 2 indésirables');

    Livewire::test(Messages::class)->call('supprimerIndesirables');

    expect($admin->fresh()->trashed())->toBeTrue()
        ->and($ia->fresh()->trashed())->toBeTrue()
        ->and($this->conversation->fresh()->trashed())->toBeFalse();

    $this->get(route('espace.messages'))->assertDontSee('indésirables');
});

it('regroupe toutes les demandes actives dans Contacts, quel que soit leur sujet', function () {
    $this->creatif->conversations()->create([
        'channel' => 'intermediate', 'subject' => 'work_B_similary', 'sender_name' => 'Similaire Un',
        'selector' => str_repeat('d', 24), 'last_message_at' => now(),
    ]);
    $this->creatif->conversations()->create([
        'channel' => 'intermediate', 'subject' => 'work_C_buy', 'sender_name' => 'Acheteur Un',
        'selector' => str_repeat('e', 24), 'last_message_at' => now(),
    ]);

    Livewire::test(Messages::class)
        ->assertSee('Client Dupont')->assertSee('Similaire Un')->assertSee('Acheteur Un')
        ->call('choisir', 'poubelle')->assertDontSee('Client Dupont')->assertDontSee('Similaire Un');
});

it('supprime une demande puis la restaure depuis la poubelle', function () {
    $composant = Livewire::test(Messages::class)
        ->call('supprimer', $this->conversation->id)
        ->assertDontSee('Client Dupont');

    expect($this->conversation->fresh()->trashed())->toBeTrue();

    $composant->call('choisir', 'poubelle')->assertSee('Client Dupont')->assertSee('Restaurer')
        ->call('supprimer', $this->conversation->id);

    expect($this->conversation->fresh()->trashed())->toBeFalse();
});

it('vide la corbeille, en supprimant pour de bon les demandes qui s\'y trouvent', function () {
    $this->conversation->delete();
    $autre = $this->creatif->conversations()->create([
        'channel' => 'book', 'sender_name' => 'Autre Client', 'selector' => str_repeat('g', 24), 'last_message_at' => now(),
    ]);
    $autre->delete();

    Livewire::test(Messages::class)
        ->call('choisir', 'poubelle')
        ->assertSee('Client Dupont')->assertSee('Autre Client')
        ->call('viderCorbeille')
        ->assertDontSee('Client Dupont')->assertDontSee('Autre Client');

    expect(\App\Models\Conversation::withTrashed()->find($this->conversation->id))->toBeNull();
    expect(\App\Models\Conversation::withTrashed()->find($autre->id))->toBeNull();
});

it('compte les non lus, tous sujets confondus, sans pastille pour la poubelle', function () {
    $this->creatif->conversations()->create([
        'channel' => 'intermediate', 'subject' => 'work_C_buy', 'sender_name' => 'Acheteur Deux',
        'selector' => str_repeat('f', 24), 'last_message_at' => now(),
    ])->messages()->create(['from_owner' => false, 'body' => 'Je veux acheter cette image.']);

    $this->get(route('espace.messages'))->assertOk()
        // Un non lu dans la conversation du beforeEach, un dans celle-ci :
        // le total du bandeau en compte deux, et Supprimer n'a pas de pastille.
        ->assertSee('2 non lus');
});

it('deplie le fil sous la ligne puis le replie au second clic', function () {
    $this->conversation->messages()->create(['from_owner' => true, 'body' => 'Volontiers, voici mon tarif.']);

    Livewire::test(Messages::class)
        ->call('basculer', $this->conversation->id)
        ->assertSet('ouvert', $this->conversation->id)
        // Les deux messages, dans l'ordre : l'expediteur puis le createur.
        ->assertSeeInOrder(['Bonjour, un devis ?', 'Volontiers, voici mon tarif.'])
        ->call('basculer', $this->conversation->id)
        ->assertSet('ouvert', null)
        ->assertDontSee('Volontiers, voici mon tarif.');
});

it('ferme la note d\'escroquerie et memorise le choix par cookie', function () {
    $this->get(route('espace.messages'))->assertOk()->assertSee('Tentatives d’escroquerie signalées');

    Livewire::test(Messages::class)
        ->assertSee('Tentatives d’escroquerie signalées')
        ->call('fermerAlerte')
        ->assertDontSee('Tentatives d’escroquerie signalées');

    expect(app('cookie')->queued('espace_messages_alerte_masquee'))->not->toBeNull();

    // Un cookie normal, chiffre comme le fait EncryptCookies en vrai :
    // c'est ce que le navigateur renverrait au prochain chargement.
    $prefixe = \Illuminate\Cookie\CookieValuePrefix::create('espace_messages_alerte_masquee', app('encrypter')->getKey());
    $chiffre = encrypt($prefixe.'1', false);

    $this->withCookie('espace_messages_alerte_masquee', $chiffre)
        ->get(route('espace.messages'))->assertOk()
        ->assertDontSee('Tentatives d’escroquerie signalées');
});

it('n\'affiche qu\'une fois des reponses identiques envoyees a la suite', function () {
    $this->conversation->messages()->create(['from_owner' => true, 'body' => 'Merci, je ne prends pas de commande.']);
    $this->conversation->messages()->create(['from_owner' => true, 'body' => 'Merci, je ne prends pas de commande.']);
    $this->conversation->messages()->create(['from_owner' => true, 'body' => 'Merci, je ne prends pas de commande.']);
    $this->conversation->messages()->create(['from_owner' => true, 'body' => 'Autre chose, en plus.']);

    $composant = Livewire::test(Messages::class)->call('basculer', $this->conversation->id);

    expect(substr_count($composant->html(), 'Merci, je ne prends pas de commande.'))->toBe(1);
    $composant->assertSee('Autre chose, en plus.');
});

it('propose une correction IA sans toucher au texte tant qu elle n est pas acceptee', function () {
    config(['services.openrouter.api_key' => 'test-key']);

    Http::fake([
        'openrouter.ai/*' => Http::response([
            'choices' => [['message' => ['content' => 'Avec plaisir, voici mon tarif.']]],
        ], 200),
    ]);

    Livewire::test(Messages::class)
        ->call('ouvrir', $this->conversation->id)
        ->set('reponse', 'avec plaisir voici mont tarif')
        ->call('corrigerReponse')
        ->assertSet('suggestionIA', 'Avec plaisir, voici mon tarif.')
        ->assertSet('reponse', 'avec plaisir voici mont tarif')
        ->call('utiliserSuggestionIA')
        ->assertSet('reponse', 'Avec plaisir, voici mon tarif.')
        ->assertSet('suggestionIA', null);
});

it('garde le texte d origine si la correction IA echoue', function () {
    config(['services.openrouter.api_key' => 'test-key']);

    Http::fake([
        'openrouter.ai/*' => Http::response(['error' => ['message' => 'Quota depasse']], 429),
    ]);

    Livewire::test(Messages::class)
        ->call('ouvrir', $this->conversation->id)
        ->set('reponse', 'Mon texte original.')
        ->call('corrigerReponse')
        ->assertSet('suggestionIA', null)
        ->assertSet('reponse', 'Mon texte original.')
        ->assertSet('erreurIA', "La correction IA n'est pas disponible pour le moment.");
});

it('affiche le label probable spam quand la detection IA l a signale', function () {
    $this->conversation->forceFill(['spam_ia' => true, 'spam_ia_probabilite' => 0.91])->save();

    $this->get(route('espace.messages'))->assertOk()->assertSee('Probable spam');
});

it('n affiche pas le label quand la conversation n a pas ete signalee', function () {
    $this->get(route('espace.messages'))->assertOk()->assertDontSee('Probable spam');
});

it('affiche seulement Contacts et Supprimer, cote a cote', function () {
    $reponse = $this->get(route('espace.messages'))->assertOk()
        ->assertSee('Contacts')->assertSee('Supprimer')
        ->assertDontSee('Similaire')->assertDontSee('Ventes');

    // Les deux boutons sont freres dans une meme nav flex, chacun a
    // largeur egale (flex-1) : le bloc prend toute la largeur.
    expect($reponse->getContent())->toContain('flex-1 items-center justify-center');
});

it('limite les corrections IA par createur', function () {
    config(['services.openrouter.api_key' => 'test-key']);
    Http::fake(['openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => 'Corrige.']]]], 200)]);

    for ($i = 0; $i < 20; $i++) {
        Illuminate\Support\Facades\RateLimiter::hit('correction-ia|'.auth()->id(), 3600);
    }

    Livewire::test(Messages::class)
        ->call('ouvrir', $this->conversation->id)
        ->set('reponse', 'Mon texte original.')
        ->call('corrigerReponse')
        ->assertSet('suggestionIA', null)
        ->assertSet('erreurIA', 'Trop de corrections demandées, réessayez dans un moment.');

    Http::assertNothingSent();
});

it('rend legitime une demande signalee', function () {
    $this->conversation->forceFill(['is_spam' => true, 'spam_ia' => true])->save();

    Livewire::test(Messages::class)->call('rendreLegitime', $this->conversation->id);

    expect($this->conversation->fresh()->is_spam)->toBeFalse()
        ->and($this->conversation->fresh()->spam_ia)->toBeFalse();
});

it('analyse les demandes au fil de l affichage et signale les spams', function () {
    config(['services.openrouter.api_key' => 'test-key', 'messagerie.spam_filter.active' => true, 'messagerie.spam_filter.seuil' => 0.6]);
    Http::fake(['openrouter.ai/*' => Http::response(['answers' => ['spam' => ['type' => 'noul', 'noul' => 0.93]]])]);

    Livewire::test(Messages::class)->call('analyser');

    $c = $this->conversation->fresh();
    expect($c->spam_ia)->toBeTrue()->and($c->spam_ia_probabilite)->toBe(0.93);

    // Deja analysee : aucun second appel.
    Livewire::test(Messages::class)->call('analyser');
    Http::assertSentCount(1);
});

it('ne reessaie pas en boucle une analyse en echec', function () {
    config(['services.openrouter.api_key' => 'test-key', 'messagerie.spam_filter.active' => true]);
    Http::fake(['openrouter.ai/*' => Http::response(['error' => ['message' => 'indisponible']], 500)]);

    Livewire::test(Messages::class)->call('analyser')
        ->assertSet('analyseEchouee', [$this->conversation->id])
        ->assertDontSeeHtml('wire:init="analyser"');

    expect($this->conversation->fresh()->spam_ia_probabilite)->toBeNull();
});
