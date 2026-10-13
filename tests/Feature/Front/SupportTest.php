<?php

use App\Mail\SupportNouveauMessage;
use App\Models\SupportFiche;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\Captcha\Captcha;
use App\Services\Support\BaseConnaissance;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/*
 | Module « Nous contacter » : depot sur /support, fil rouvert par lien signe.
 */

beforeEach(function () {
    Mail::fake();
});

function url_support(string $chemin = '/contact'): string
{
    return 'https://'.config('ubdf.book_domain').$chemin;
}

it('affiche la page avec e-mail et captcha pour un anonyme', function () {
    $this->get(url_support())->assertOk()
        ->assertSee('Nous contacter')->assertSee('name="email"', false)->assertSee('name="captcha"', false);
});

it('refuse un anonyme sans e-mail ni bon captcha', function () {
    app(Captcha::class)->generer('support');

    $this->post(url_support(), ['message' => 'Bonjour, mon book ne s’affiche plus.', 'captcha' => 'ZZZZ'])
        ->assertSessionHasErrors(['email', 'captcha']);

    expect(SupportTicket::count())->toBe(0);
});

it('enregistre la demande d un anonyme et previent l admin', function () {
    $code = app(Captcha::class)->generer('support');

    $reponse = $this->post(url_support(), [
        'message' => 'Bonjour, mon book ne s’affiche plus.',
        'email' => 'claire@example.com',
        'captcha' => $code,
    ]);

    $ticket = SupportTicket::sole();
    $reponse->assertRedirect($ticket->lien());

    expect($ticket->email)->toBe('claire@example.com')
        ->and($ticket->statut)->toBe('nouveau')
        ->and($ticket->messages)->toHaveCount(1);
    Mail::assertSent(SupportNouveauMessage::class);
});

it('identifie le creatif connecte sans e-mail ni captcha', function () {
    $creatif = User::factory()->create(['login' => 'ariane', 'brand' => 'ub']);

    $this->actingAs($creatif, 'web')->get(url_support())->assertOk()
        ->assertSee('ariane')->assertDontSee('name="captcha"', false);

    $this->actingAs($creatif, 'web')->post(url_support(), ['message' => 'Comment changer de modèle ?']);

    expect(SupportTicket::sole()->user_id)->toBe($creatif->id);
});

it('rouvre le fil par le lien signe et refuse un lien trafique', function () {
    $ticket = SupportTicket::create(['email' => 'claire@example.com']);
    $ticket->ajouter('client', 'Première question');
    $ticket->ajouter('admin', 'Réponse du support');

    $this->get($ticket->lien())->assertOk()->assertSee('Première question')->assertSee('Réponse du support');
    $this->get(url_support('/contact/fil/'.$ticket->id))->assertForbidden();

    $this->post($ticket->lien(), ['message' => 'Merci, une autre question.'])->assertRedirect($ticket->lien());

    expect($ticket->fresh()->statut)->toBe('nouveau')
        ->and($ticket->messages()->count())->toBe(3);
});

it('ne retient que les fiches actives qui partagent un mot', function () {
    SupportFiche::create(['question' => 'Comment changer de modèle de book ?', 'reponse' => 'Dans Habillage.', 'actif' => true]);
    SupportFiche::create(['question' => 'Comment changer de modèle ?', 'reponse' => 'Brouillon.', 'actif' => false]);
    SupportFiche::create(['question' => 'Comment payer ?', 'reponse' => 'Par carte.', 'actif' => true]);

    $fiches = app(BaseConnaissance::class)->rechercher('Je voudrais changer le modèle de mon book');

    expect($fiches->pluck('reponse')->all())->toBe(['Dans Habillage.']);
});

it('affiche le menu support des la premiere demande, pastille tant que la reponse n est pas lue', function () {
    $creatif = User::factory()->create(['login' => 'ariane', 'brand' => 'ub']);
    expect(SupportTicket::dernierePour($creatif))->toBeNull();

    $ticket = SupportTicket::create(['user_id' => $creatif->id]);
    $ticket->ajouter('client', 'Question');
    expect(SupportTicket::dernierePour($creatif)->id)->toBe($ticket->id)
        ->and($ticket->fresh()->reponseNonLue())->toBeFalse();

    $ticket->ajouter('admin', 'Réponse');
    expect($ticket->fresh()->reponseNonLue())->toBeTrue();

    $this->get($ticket->lien())->assertOk();
    expect($ticket->fresh()->reponseNonLue())->toBeFalse();
});

it('clot la discussion avec la note du client', function () {
    $ticket = SupportTicket::create(['email' => 'claire@example.com']);
    $ticket->ajouter('client', 'Question');
    $ticket->ajouter('admin', 'Réponse');

    $this->get($ticket->lien())->assertOk()->assertSee('Résolu')->assertSee('Ajouter un message');
    $this->post($ticket->lienResolu(), ['note' => 4])->assertRedirect($ticket->lien());

    expect($ticket->fresh())->statut->toBe('clos')->note->toBe(4);
    $this->get($ticket->lien())->assertSee('Cette discussion est close.')->assertDontSee('Ajouter un message');
    $this->post(url_support('/contact/fil/'.$ticket->id.'/resolu'), ['note' => 1])->assertForbidden();
});

it('ne rattache une discussion par l adresse qu a un compte dont l adresse est prouvee', function () {
    $ticket = SupportTicket::create(['email' => 'claire@example.com']);
    $ticket->ajouter('client', 'Question confidentielle');

    $usurpateur = \App\Models\Visitor::create(['email' => 'claire@example.com', 'password' => 'mot-de-passe-long']);
    expect(SupportTicket::dernierePour($usurpateur))->toBeNull();

    $usurpateur->forceFill(['email_verified_at' => now()])->save();
    expect(SupportTicket::dernierePour($usurpateur)?->id)->toBe($ticket->id);
});

it('ne rouvre pas une discussion close par un envoi direct', function () {
    $ticket = SupportTicket::create(['email' => 'claire@example.com']);
    $ticket->ajouter('client', 'Question');
    $ticket->update(['statut' => 'clos']);

    $this->post($ticket->lien(), ['message' => 'Encore moi'])->assertRedirect($ticket->lien());

    expect($ticket->fresh()->statut)->toBe('clos')->and($ticket->messages()->count())->toBe(1);
});

it('n interprete pas le texte du client comme du Markdown dans les mails', function () {
    $ticket = SupportTicket::create(['email' => 'claire@example.com']);
    $ticket->ajouter('client', "Voir [ici](https://piege.example)\n\n<script>x</script>");

    $html = (new \App\Mail\SupportNouveauMessage($ticket))->render();

    expect($html)->not->toContain('href="https://piege.example"')->not->toContain('<script>x');
});

it('range la piece jointe du createur dans son book et ne la sert que par lien signe', function () {
    Storage::fake('public');
    $creatif = User::factory()->create(['login' => 'ariane', 'brand' => 'ub']);

    $this->actingAs($creatif, 'web')->post(url_support(), [
        'message' => 'Voici une capture du problème.',
        'piece' => UploadedFile::fake()->image('capture.png'),
    ]);

    $message = SupportTicket::sole()->messages->first();
    expect($message->piece)->toStartWith('books/a/r/i/ariane/support/')
        ->and($message->piece_nom)->toBe('capture.png');
    Storage::disk('public')->assertExists($message->piece);

    $this->get($message->lienPiece())->assertOk();
    $this->get(url_support('/contact/piece/'.$message->id))->assertForbidden();
});

it('refuse une piece jointe d un anonyme et un fichier SVG', function () {
    Storage::fake('public');
    $code = app(Captcha::class)->generer('support');

    $this->post(url_support(), [
        'message' => 'Bonjour, voici un fichier.', 'email' => 'claire@example.com', 'captcha' => $code,
        'piece' => UploadedFile::fake()->image('a.png'),
    ])->assertSessionHasErrors('piece');

    $creatif = User::factory()->create(['login' => 'ariane', 'brand' => 'ub']);
    $this->actingAs($creatif, 'web')->post(url_support(), [
        'message' => 'Voici un fichier vectoriel.',
        'piece' => UploadedFile::fake()->createWithContent('x.svg', '<svg onload="alert(1)"/>'),
    ])->assertSessionHasErrors('piece');
});

it('reserve au compte connecte une demande anonyme faite avec son adresse', function () {
    $creatif = User::factory()->create(['login' => 'adolie', 'email' => 'adolie@me.com', 'brand' => 'ub']);
    $code = app(Captcha::class)->generer('support');

    $this->post(url_support(), ['message' => 'Donnez-moi les infos de mon compte.', 'email' => 'adolie@me.com', 'captcha' => $code])
        ->assertRedirect();
    $ticket = SupportTicket::sole();
    expect($ticket->compte_requis)->toBeTrue()->and($ticket->user_id)->toBe($creatif->id);

    // La confirmation immediate reste visible ; ensuite, plus sans connexion.
    $this->withSession(['envoye' => $ticket->id])->get($ticket->lien())->assertOk()->assertSee('connectez-vous pour lire la réponse');
    $ticket->ajouter('admin', 'Réponse confidentielle');
    $this->get($ticket->lien())->assertOk()->assertSee('Connectez-vous pour la lire.')->assertDontSee('Réponse confidentielle');
    $this->post($ticket->lien(), ['message' => 'Encore'])->assertForbidden();

    $this->actingAs($creatif, 'web')->get($ticket->lien())->assertOk()->assertSee('Réponse confidentielle');
});

it('ne laisse pas la confirmation d un envoi ouvrir une autre discussion verrouillee', function () {
    User::factory()->create(['login' => 'adolie', 'email' => 'adolie@me.com', 'brand' => 'ub']);
    $verrouille = SupportTicket::create(['email' => 'adolie@me.com', 'compte_requis' => true]);
    $verrouille->ajouter('client', 'Question');
    $verrouille->ajouter('admin', 'Réponse confidentielle');

    $autre = SupportTicket::create(['email' => 'pirate@example.com']);

    $this->withSession(['envoye' => $autre->id])->get($verrouille->lien())
        ->assertOk()->assertSee('Connectez-vous pour la lire.')->assertDontSee('Réponse confidentielle');
});

it('sert la piece jointe a l admin et l isole dans un bac a sable', function () {
    Storage::fake('public');
    $creatif = User::factory()->create(['login' => 'ariane', 'brand' => 'ub']);
    $ticket = SupportTicket::create(['user_id' => $creatif->id, 'compte_requis' => true]);
    $message = $ticket->ajouter('client', 'Capture', piece: UploadedFile::fake()->image('cap%ture.png'));

    expect($message->piece_nom)->toBe('cap-ture.png');
    $this->get($message->lienPiece())->assertForbidden();

    $admin = \App\Models\Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']);
    $this->actingAs($admin, 'admin')->get($message->lienPiece())
        ->assertOk()->assertHeader('Content-Security-Policy', 'sandbox');
});
