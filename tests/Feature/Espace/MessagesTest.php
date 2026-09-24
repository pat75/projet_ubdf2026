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

it('range les demandes par dossier selon leur sujet', function () {
    $this->creatif->conversations()->create([
        'channel' => 'intermediate', 'subject' => 'work_B_similary', 'sender_name' => 'Similaire Un',
        'selector' => str_repeat('d', 24), 'last_message_at' => now(),
    ]);
    $vente = $this->creatif->conversations()->create([
        'channel' => 'intermediate', 'subject' => 'work_C_buy', 'sender_name' => 'Acheteur Un',
        'selector' => str_repeat('e', 24), 'last_message_at' => now(),
    ]);

    Livewire::test(Messages::class)
        ->assertSee('Client Dupont')->assertDontSee('Similaire Un')->assertDontSee('Acheteur Un')
        ->call('choisir', 'similaire')->assertSee('Similaire Un')->assertDontSee('Client Dupont')
        ->call('choisir', 'ventes')->assertSee('Acheteur Un')->assertDontSee('Similaire Un');

    expect($vente->subject)->toBe('work_C_buy');
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

it('compte les non lus par dossier, sans pastille pour la poubelle', function () {
    $this->creatif->conversations()->create([
        'channel' => 'intermediate', 'subject' => 'work_C_buy', 'sender_name' => 'Acheteur Deux',
        'selector' => str_repeat('f', 24), 'last_message_at' => now(),
    ])->messages()->create(['from_owner' => false, 'body' => 'Je veux acheter cette image.']);

    $this->get(route('espace.messages'))->assertOk()
        // Un non lu dans « Contacts » (la conversation du beforeEach),
        // un dans « Ventes » : le total du bandeau en compte deux.
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
