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
