<?php

use App\Livewire\Espace\Messages;
use App\Mail\ReponseRecue;
use App\Models\User;
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

    Mail::assertSent(ReponseRecue::class, fn ($m) => $m->hasTo('client@example.test'));
});

it('masque les indesirables et les demandes des autres', function () {
    $autre = User::factory()->create()->conversations()->create(['channel' => 'book', 'sender_name' => 'Autre', 'selector' => str_repeat('b', 24)]);
    $this->creatif->conversations()->create(['channel' => 'book', 'sender_name' => 'Spammeur', 'is_spam' => true, 'selector' => str_repeat('c', 24)]);

    $this->get(route('espace.messages'))->assertDontSee('Spammeur')->assertDontSee('Autre');
    Livewire::test(Messages::class)->call('ouvrir', $autre->id)->assertNotFound();
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

it('affiche seulement Contacts et Supprimer, cote a cote', function () {
    $reponse = $this->get(route('espace.messages'))->assertOk()
        ->assertSee('Contacts')->assertSee('Supprimer')
        ->assertDontSee('Similaire')->assertDontSee('Ventes');

    // Les deux boutons sont freres dans une meme nav flex, chacun a
    // largeur egale (flex-1) : le bloc prend toute la largeur.
    expect($reponse->getContent())->toContain('flex-1 items-center justify-center');
});
