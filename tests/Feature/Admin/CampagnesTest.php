<?php

use App\Mail\CampagneMail;
use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\User;
use App\Services\Messagerie\EnvoiCampagne;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();

    $this->campagne = Campaign::create([
        'brand' => 'ub', 'type' => 'newsletter', 'name' => 'Rentrée',
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

it('porte un lien de desabonnement qui coupe la newsletter', function () {
    $abonne = abonneNewsletter(['email' => 'oui@example.test', 'login' => 'ariane']);

    $lien = app(EnvoiCampagne::class)->lienDesabonnement($abonne);

    $this->get($lien)->assertOk()->assertSee('ne recevrez plus');

    expect($abonne->bookSetting()->first()->diffuse_newsletter)->toBeFalse();
});

it('refuse un lien de desabonnement sans signature valable', function () {
    $abonne = abonneNewsletter(['login' => 'ariane']);

    $this->get(route('newsletter.desabonnement', ['user' => $abonne->login]))->assertForbidden();

    expect($abonne->bookSetting()->first()->diffuse_newsletter)->toBeTrue();
});

it('nettoie le contenu redige au back-office', function () {
    $this->campagne->update(['body' => '<p>Bonjour</p><script>alert(1)</script>']);

    expect($this->campagne->fresh()->body)->toContain('Bonjour')->not->toContain('script');
});

it('envoie un essai sans rien enregistrer', function () {
    abonneNewsletter(['email' => 'oui@example.test']);

    app(EnvoiCampagne::class)->essai($this->campagne, 'moi@example.test', User::first());

    Mail::assertQueued(CampagneMail::class, fn ($m) => $m->hasTo('moi@example.test'));
    expect(CampaignSend::count())->toBe(0);
});
