<?php

use App\Models\User;
use App\Mail\ReponseRecue;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->creatif = User::factory()->create();
    $this->conversation = $this->creatif->conversations()->create([
        'channel' => 'book', 'subject' => 'autre', 'sender_name' => 'Client Dupont',
        'sender_email' => 'client@example.test', 'selector' => str_repeat('a', 24), 'last_message_at' => now(),
    ]);
    $this->conversation->messages()->create(['from_owner' => false, 'body' => 'Bonjour, un devis ?']);
});

it('liste les demandes avec le nombre de non lus', function () {
    Sanctum::actingAs($this->creatif, ['messages:lire']);

    $this->getJson('/api/v1/messages')
        ->assertOk()
        ->assertJsonPath('data.0.expediteur', 'Client Dupont')
        ->assertJsonPath('data.0.non_lus', 1)
        ->assertJsonPath('non_lus', 1)
        ->assertJsonMissingPath('data.0.owner_token');
});

it('ouvre un fil et le marque lu', function () {
    Sanctum::actingAs($this->creatif, ['messages:lire']);

    $this->getJson("/api/v1/messages/{$this->conversation->id}")
        ->assertOk()
        ->assertJsonPath('data.messages.0.texte', 'Bonjour, un devis ?')
        ->assertJsonPath('data.messages.0.du_creatif', false);

    expect($this->conversation->messages()->whereNull('read_at')->count())->toBe(0);
});

it('liste la poubelle a part', function () {
    $this->conversation->delete();
    Sanctum::actingAs($this->creatif, ['messages:lire']);

    $this->getJson('/api/v1/messages')->assertJsonCount(0, 'data');
    $this->getJson('/api/v1/messages?dossier=poubelle')->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/messages?dossier=pirate')->assertUnprocessable();
});

it('ne montre pas la demande d un autre creatif', function () {
    Sanctum::actingAs(User::factory()->create(), ['messages:lire']);

    $this->getJson("/api/v1/messages/{$this->conversation->id}")->assertNotFound();
});

it('exige le droit messages:lire', function () {
    Sanctum::actingAs($this->creatif, ['images:lire']);

    $this->getJson('/api/v1/messages')->assertForbidden();
});

it('repond a une demande et previent l emetteur', function () {
    Mail::fake();
    Sanctum::actingAs($this->creatif, ['messages:lire', 'messages:ecrire']);

    $this->postJson("/api/v1/messages/{$this->conversation->id}/reponses", ['texte' => 'Avec plaisir.'])
        ->assertOk()
        ->assertJsonPath('data.messages.1.texte', 'Avec plaisir.')
        ->assertJsonPath('data.messages.1.du_creatif', true);

    Mail::assertSent(ReponseRecue::class, fn ($m) => $m->hasTo('client@example.test'));
});

it('refuse une reponse vide', function () {
    Sanctum::actingAs($this->creatif, ['messages:ecrire']);

    $this->postJson("/api/v1/messages/{$this->conversation->id}/reponses", ['texte' => ''])
        ->assertUnprocessable()->assertJsonValidationErrors('texte');
});

it('met a la poubelle, restaure, puis vide la poubelle', function () {
    Sanctum::actingAs($this->creatif, ['messages:ecrire']);

    $this->deleteJson("/api/v1/messages/{$this->conversation->id}")->assertNoContent();
    expect($this->conversation->fresh()->trashed())->toBeTrue();

    $this->postJson("/api/v1/messages/{$this->conversation->id}/restaurer")->assertNoContent();
    expect($this->conversation->fresh()->trashed())->toBeFalse();

    $this->deleteJson("/api/v1/messages/{$this->conversation->id}");
    $this->deleteJson('/api/v1/messages/poubelle')->assertNoContent();
    expect(App\Models\Conversation::withTrashed()->count())->toBe(0);
});

it('exige le droit messages:ecrire', function () {
    Sanctum::actingAs($this->creatif, ['messages:lire']);

    $this->postJson("/api/v1/messages/{$this->conversation->id}/reponses", ['texte' => 'Bonjour'])->assertForbidden();
});
