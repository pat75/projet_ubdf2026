<?php

use App\Jobs\EvaluerSpamIAConversation;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->creatif = User::factory()->create();
    $this->conversation = $this->creatif->conversations()->create([
        'channel' => 'intermediate', 'sender_name' => 'Client', 'selector' => str_repeat('a', 24), 'last_message_at' => now(),
    ]);
    $this->conversation->messages()->create(['from_owner' => false, 'body' => 'Un message a evaluer.']);
});

it('enregistre la probabilite et le seuil franchi sur la conversation', function () {
    config(['services.openrouter.api_key' => 'test-key', 'messagerie.spam_filter.active' => true, 'messagerie.spam_filter.seuil' => 0.6]);

    Http::fake(['openrouter.ai/*' => Http::response(['answers' => ['spam' => ['type' => 'noul', 'noul' => 0.82]]], 200)]);

    (new EvaluerSpamIAConversation($this->conversation))->handle(app(App\Services\Messagerie\DetecteurSpamIA::class));

    expect($this->conversation->fresh())
        ->spam_ia_probabilite->toBe(0.82)
        ->spam_ia->toBeTrue();
});

it('ne marque pas spam sous le seuil', function () {
    config(['services.openrouter.api_key' => 'test-key', 'messagerie.spam_filter.active' => true, 'messagerie.spam_filter.seuil' => 0.6]);

    Http::fake(['openrouter.ai/*' => Http::response(['answers' => ['spam' => ['type' => 'noul', 'noul' => 0.2]]], 200)]);

    (new EvaluerSpamIAConversation($this->conversation))->handle(app(App\Services\Messagerie\DetecteurSpamIA::class));

    expect($this->conversation->fresh())->spam_ia->toBeFalse();
});

it('ne touche a rien si la fonctionnalite est desactivee', function () {
    config(['messagerie.spam_filter.active' => false]);

    (new EvaluerSpamIAConversation($this->conversation))->handle(app(App\Services\Messagerie\DetecteurSpamIA::class));

    expect($this->conversation->fresh())->spam_ia->toBeNull();
});
