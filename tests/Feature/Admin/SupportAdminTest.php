<?php

use App\Filament\Resources\SupportTickets\Pages\ViewSupportTicket;
use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Jobs\ProposerFicheSupport;
use App\Mail\SupportReponse;
use App\Models\Admin;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']);
    $this->creatif = User::factory()->create(['login' => 'ariane', 'brand' => 'ub']);
    $this->ticket = SupportTicket::create(['user_id' => $this->creatif->id]);
    $this->ticket->ajouter('client', 'Mon book ne s’affiche plus.');
});

it('liste les demandes et ouvre le fil', function () {
    $this->actingAs($this->admin, 'admin')->get(SupportTicketResource::getUrl())->assertOk()->assertSee('ariane');
    $this->actingAs($this->admin, 'admin')->get(SupportTicketResource::getUrl('view', ['record' => $this->ticket]))
        ->assertOk()->assertSee('Mon book ne s’affiche plus.')->assertSee('Se connecter au compte');
});

it('envoie la reponse par mail et propose une fiche', function () {
    Mail::fake();
    Bus::fake();

    $this->actingAs($this->admin, 'admin');
    Livewire::test(ViewSupportTicket::class, ['record' => $this->ticket->id])
        ->set('reponse', 'Videz le cache de votre navigateur.')
        ->call('envoyer')
        ->assertHasNoErrors()
        ->assertSet('reponse', '');

    expect($this->ticket->fresh()->statut)->toBe('repondu')
        ->and($this->ticket->messages()->reorder()->latest('id')->first()->admin_id)->toBe($this->admin->id);
    Mail::assertSent(SupportReponse::class, fn ($mail) => $mail->hasTo($this->creatif->email));
    Bus::assertDispatchedAfterResponse(ProposerFicheSupport::class);
});
