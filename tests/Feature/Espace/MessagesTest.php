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
