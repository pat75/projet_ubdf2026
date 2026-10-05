<?php

use App\Filament\Resources\Conversations\Pages\ListConversations;
use App\Models\Admin;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Livewire\Livewire;

it('montre le texte du message dans la bulle d aide du bouton de lecture', function () {
    $this->actingAs(Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']), 'admin');
    $c = Conversation::create(['user_id' => User::factory()->create()->id, 'channel' => 'book', 'subject' => 'x', 'sender_name' => 'Léa']);
    Message::create(['conversation_id' => $c->id, 'from_owner' => false, 'body' => 'Bonjour, devis svp']);

    Livewire::test(ListConversations::class)
        ->assertTableActionExists('lire', fn ($action) => $action->getTooltip() === 'Bonjour, devis svp', $c);
});

it('analyse par IA les demandes de la page affichee', function () {
    config(['services.openrouter.api_key' => 'test-key', 'messagerie.spam_filter.active' => true, 'messagerie.spam_filter.seuil' => 0.6]);
    Illuminate\Support\Facades\Http::fake(['openrouter.ai/*' => Illuminate\Support\Facades\Http::response(['answers' => ['spam' => ['type' => 'noul', 'noul' => 0.8]]])]);
    $this->actingAs(Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']), 'admin');
    $c = Conversation::create(['user_id' => User::factory()->create()->id, 'channel' => 'book', 'subject' => 'x', 'sender_name' => 'Léa']);
    Message::create(['conversation_id' => $c->id, 'from_owner' => false, 'body' => 'Achetez des followers']);

    Livewire::test(ListConversations::class)
        ->assertSeeHtml('wire:init="analyserSpamIA"')
        ->call('analyserSpamIA')
        ->assertDontSeeHtml('wire:init="analyserSpamIA"');

    expect($c->fresh()->spam_ia)->toBeTrue();
});
