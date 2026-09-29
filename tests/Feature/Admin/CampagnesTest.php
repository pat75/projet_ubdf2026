<?php

use App\Mail\CampagneMail;
use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\NewsletterMail;
use App\Models\User;
use App\Services\Messagerie\EnvoiCampagne;
use App\Services\Newsletter\Desabonnement;
use App\Services\Newsletter\Destinataires;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Mail::fake();

    $this->campagne = Campaign::create([
        'brand' => 'ub', 'type' => 'newsletter', 'name' => 'Rentrée',
        'cibles' => ['creatifs'],
        'subject' => 'Les books du mois', 'body' => '<p>Bonjour</p>',
    ]);
});

function abonneNewsletter(array $attrs = [], bool $abonne = true): User
{
    $creatif = User::factory()->create($attrs + ['brand' => 'ub']);
    $creatif->bookSetting()->create(['theme' => 'mdl_2016_zoom', 'diffuse_newsletter' => $abonne]);

    return $creatif;
}

it('envoie aux abonnes de la marque, une seule fois', function () {
    $abonne = abonneNewsletter(['email' => 'oui@example.test']);
    abonneNewsletter(['email' => 'non@example.test'], abonne: false);
    abonneNewsletter(['email' => 'df@example.test'])->update(['brand' => 'df']);

    $envoyes = app(EnvoiCampagne::class)->envoyer($this->campagne);

    expect($envoyes)->toBe(1)
        ->and(CampaignSend::where('campaign_id', $this->campagne->id)->sole()->user_id)->toBe($abonne->id)
        ->and($this->campagne->fresh()->sent_at)->not->toBeNull();

    Mail::assertQueued(CampagneMail::class, fn ($m) => $m->hasTo('oui@example.test'));
    Mail::assertNotQueued(CampagneMail::class, fn ($m) => $m->hasTo('non@example.test'));
    Mail::assertNotQueued(CampagneMail::class, fn ($m) => $m->hasTo('df@example.test'));

    // Relancer l'envoi ne redonne pas le message.
    expect(app(EnvoiCampagne::class)->envoyer($this->campagne))->toBe(0);
    Mail::assertQueuedCount(1);
});

it('ne touche pas une cible qui n’est pas cochee', function () {
    abonneNewsletter(['email' => 'creatif@example.test']);
    NewsletterMail::create(['email' => 'visiteur@example.test', 'brand' => 'ub']);

    app(EnvoiCampagne::class)->envoyer($this->campagne);

    Mail::assertQueued(CampagneMail::class, fn ($m) => $m->hasTo('creatif@example.test'));
    Mail::assertNotQueued(CampagneMail::class, fn ($m) => $m->hasTo('visiteur@example.test'));
});

it('fusionne les deux publics sans doublon d’adresse', function () {
    $creatif = abonneNewsletter(['email' => 'commune@example.test']);
    NewsletterMail::create(['email' => 'commune@example.test', 'brand' => 'ub']);
    NewsletterMail::create(['email' => 'seule@example.test', 'brand' => 'ub']);
    NewsletterMail::create(['email' => 'partie@example.test', 'brand' => 'ub', 'desabonne_at' => now()]);
    NewsletterMail::create(['email' => 'ailleurs@example.test', 'brand' => 'df']);

    $this->campagne->update(['cibles' => ['creatifs', 'visiteurs']]);

    $destinataires = app(Destinataires::class)->pour($this->campagne);

    expect($destinataires->pluck('email')->sort()->values()->all())
        ->toBe(['commune@example.test', 'seule@example.test'])
        // L'adresse commune est servie du cote createur : c'est lui qui a un prenom.
        ->and($destinataires->firstWhere('email', 'commune@example.test')->userId)->toBe($creatif->id);

    expect(app(EnvoiCampagne::class)->envoyer($this->campagne))->toBe(2);
    Mail::assertQueuedCount(2);
});

it('remplace {prenom} dans le corps du message', function () {
    $this->campagne->update(['body' => '<p>Bonjour {prenom},</p>']);
    abonneNewsletter(['email' => 'oui@example.test', 'firstname' => 'Camille']);

    app(EnvoiCampagne::class)->envoyer($this->campagne);

    Mail::assertQueued(CampagneMail::class, fn (CampagneMail $m) => str_contains($m->corps(), 'Bonjour Camille,'));
});

it('desabonne les deux publics depuis un seul lien signe', function () {
    $creatif = abonneNewsletter(['email' => 'commune@example.test']);
    NewsletterMail::create(['email' => 'commune@example.test', 'brand' => 'ub']);

    $lien = app(Desabonnement::class)->lien('commune@example.test');

    $this->get($lien)->assertOk()->assertSee('ne recevrez plus');

    expect($creatif->bookSetting()->first()->diffuse_newsletter)->toBeFalse()
        ->and(NewsletterMail::where('email', 'commune@example.test')->sole()->desabonne_at)->not->toBeNull();
});

it('permet de revenir sur un desabonnement', function () {
    $creatif = abonneNewsletter(['email' => 'erreur@example.test']);

    $this->get(app(Desabonnement::class)->lien('erreur@example.test'))->assertOk();
    $this->get(app(Desabonnement::class)->lien('erreur@example.test', 'newsletter.reabonnement'))
        ->assertOk()->assertSee('réabonné');

    expect($creatif->bookSetting()->first()->diffuse_newsletter)->toBeTrue();
});

it('refuse un lien de desinscription sans signature valable', function () {
    $creatif = abonneNewsletter(['email' => 'oui@example.test']);
    $jeton = app(Desabonnement::class)->encoder('oui@example.test');

    $this->get(route('newsletter.desinscription', ['adresse' => $jeton]))->assertForbidden();

    expect($creatif->bookSetting()->first()->diffuse_newsletter)->toBeTrue();
});

it('nettoie le contenu redige au back-office', function () {
    $this->campagne->update(['body' => '<p>Bonjour</p><script>alert(1)</script>']);

    expect($this->campagne->fresh()->body)->toContain('Bonjour')->not->toContain('script');
});

it('envoie un essai sans rien enregistrer, et en garde la date', function () {
    abonneNewsletter(['email' => 'oui@example.test']);

    app(EnvoiCampagne::class)->essai($this->campagne, 'moi@example.test', User::first());

    Mail::assertQueued(CampagneMail::class, fn ($m) => $m->hasTo('moi@example.test'));
    expect(CampaignSend::count())->toBe(0)
        ->and($this->campagne->fresh()->essai_at)->not->toBeNull();
});

it('envoie les newsletters programmees dont l’heure est passee', function () {
    abonneNewsletter(['email' => 'oui@example.test']);
    $this->campagne->update(['scheduled_at' => now()->subMinute()]);

    $plusTard = Campaign::create([
        'brand' => 'ub', 'type' => 'newsletter', 'name' => 'Demain', 'cibles' => ['creatifs'],
        'subject' => 'Plus tard', 'body' => '<p>…</p>', 'scheduled_at' => now()->addDay(),
    ]);

    $this->artisan('ubdf:envoyer-newsletters')->assertSuccessful();

    expect($this->campagne->fresh()->sent_at)->not->toBeNull()
        ->and($plusTard->fresh()->sent_at)->toBeNull();
    Mail::assertQueuedCount(1);
});

it('decoupe l’envoi en paquets', function () {
    Queue::fake();

    foreach (range(1, 3) as $i) {
        abonneNewsletter(['email' => 'creatif'.$i.'@example.test']);
    }

    app(EnvoiCampagne::class)->envoyer($this->campagne);

    Queue::assertPushed(\App\Jobs\Newsletter\EnvoyerPaquetNewsletter::class, 1);
});
