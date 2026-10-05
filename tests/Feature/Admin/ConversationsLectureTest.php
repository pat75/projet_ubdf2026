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

it('signale dans le back-office que Jev est indisponible', function () {
    config(['services.openrouter.api_key' => 'test-key', 'messagerie.spam_filter.active' => true]);
    Illuminate\Support\Facades\Http::fake(['openrouter.ai/*' => Illuminate\Support\Facades\Http::response(['error' => ['message' => 'indisponible']], 503)]);
    app(App\Services\Messagerie\DetecteurSpamIA::class)->evaluer('Bonjour');
    $this->actingAs(Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']), 'admin');

    Livewire::test(ListConversations::class)
        ->assertSee('Analyse IA des spams suspendue')
        ->assertDontSeeHtml('wire:init="analyserSpamIA"');
});

it('affiche l avatar colle au nom du createur, et celui de l emetteur quand il a un compte', function () {
    $this->actingAs(Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']), 'admin');
    $createur = User::factory()->create(['login' => 'ariane9', 'firstname' => 'Ariane', 'lastname' => 'Martin']);
    App\Models\Visitor::factory()->create(['email' => 'lea@example.test']);
    Conversation::create(['user_id' => $createur->id, 'channel' => 'book', 'subject' => 'x', 'sender_name' => 'Léa', 'sender_email' => 'lea@example.test']);
    Conversation::create(['user_id' => $createur->id, 'channel' => 'book', 'subject' => 'x', 'sender_name' => 'Inconnu', 'sender_email' => 'inconnu@example.test']);

    $html = Livewire::test(ListConversations::class)->assertSuccessful()
        ->assertSeeHtml('<span class="ub-personne-titre">ariane9</span><span class="ub-personne-dessous">Ariane Martin</span>')
        ->html();

    // Deux lignes : 2 avatars de createur + 1 d'emetteur (« Inconnu » n'a pas de compte).
    expect(substr_count($html, 'class="ub-personne-avatar"'))->toBe(3);
});
