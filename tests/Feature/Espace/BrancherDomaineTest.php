<?php

use App\Livewire\Espace\MonDomaine;
use App\Livewire\Espace\NomDeDomaine;
use App\Models\Domaine;
use App\Models\User;
use App\Services\Domaines\Dns;
use Livewire\Livewire;

beforeEach(function () {
    $this->creatif = User::factory()->create(['login' => 'jeanne']);
    $this->actingAs($this->creatif);
    config(['services.ovh.ip_vps' => '203.0.113.10']);
});

/** DNS simule : TXT et A publies pour un nom. */
function dnsSimule(array $txt = [], array $a = []): void
{
    $dns = Mockery::mock(Dns::class);
    $dns->shouldReceive('txt')->andReturnUsing(fn (string $nom) => $txt[$nom] ?? []);
    $dns->shouldReceive('ipv4')->andReturnUsing(fn (string $nom) => $a[$nom] ?? []);
    app()->instance(Dns::class, $dns);
}

it('declare un domaine deja possede et affiche les reglages DNS', function () {
    Livewire::test(NomDeDomaine::class)->set('existant', 'https://www.Jeanne-Dupont.fr/')->call('brancher')
        ->assertHasNoErrors()->assertRedirect(route('espace.domaine'));

    $domaine = Domaine::sole();
    expect($domaine)->nom->toBe('jeanne-dupont.fr')->source->toBe('externe')->statut->toBe('a_verifier')
        ->and(strlen($domaine->jeton))->toBe(32);

    $this->get(route('espace.domaine'))->assertOk()
        ->assertSee('_ultra-book.jeanne-dupont.fr')->assertSee('ultra-book-verification='.$domaine->jeton)
        ->assertSee('203.0.113.10')->assertDontSee('Transférer mon domaine')->assertDontSee('Résilier mon domaine');
});

it('refuse un nom invalide, un domaine de la plateforme ou deja utilise', function () {
    Domaine::create(['user_id' => User::factory()->create()->id, 'nom' => 'pris.fr', 'statut' => 'actif', 'source' => 'ovh']);

    foreach (['pas un domaine', 'jeanne.'.config('marques.marques.ub.domaine_books'), 'pris.fr'] as $saisie) {
        Livewire::test(NomDeDomaine::class)->set('existant', $saisie)->call('brancher')->assertHasErrors('existant');
    }
    expect(Domaine::where('user_id', $this->creatif->id)->count())->toBe(0);
});

it('laisse le vrai proprietaire reprendre une declaration jamais prouvee', function () {
    $intrus = User::factory()->create();
    Domaine::create(['user_id' => $intrus->id, 'nom' => 'jeanne-dupont.fr', 'statut' => 'a_verifier', 'source' => 'externe', 'jeton' => 'x']);

    Livewire::test(NomDeDomaine::class)->set('existant', 'jeanne-dupont.fr')->call('brancher')->assertHasNoErrors();

    expect(Domaine::sole()->user_id)->toBe($this->creatif->id);
});

it('ne sert le domaine qu une fois le TXT prouve et le DNS pointe vers le VPS', function () {
    Livewire::test(NomDeDomaine::class)->set('existant', 'jeanne-dupont.fr')->call('brancher');
    $domaine = Domaine::sole();
    $txt = $domaine->enregistrementTxt();

    // A deja vers le VPS mais pas de TXT : pas servi.
    dnsSimule([], ['jeanne-dupont.fr' => ['203.0.113.10']]);
    $this->artisan('ubdf:verifier-domaines');
    expect($domaine->fresh()->statut)->toBe('a_verifier');
    $this->getJson('/api/v1/domaines/autorise?domain=jeanne-dupont.fr')->assertNotFound();

    dnsSimule([$txt['nom'] => [$txt['valeur']]], ['jeanne-dupont.fr' => ['203.0.113.10']]);
    $this->artisan('ubdf:verifier-domaines');
    expect($domaine->fresh()->statut)->toBe('actif');
    $this->getJson('/api/v1/domaines/autorise?domain=jeanne-dupont.fr')->assertOk();
});

it('verifie a la demande puis retire le domaine', function () {
    Livewire::test(NomDeDomaine::class)->set('existant', 'jeanne-dupont.fr')->call('brancher');
    $txt = Domaine::sole()->enregistrementTxt();

    dnsSimule();
    Livewire::test(MonDomaine::class)->call('verifierMaintenant')->assertSet('erreur', fn ($e) => str_contains($e, 'TXT'));

    dnsSimule([$txt['nom'] => [$txt['valeur']]]);
    Livewire::test(MonDomaine::class)->call('verifierMaintenant')->assertSet('erreur', null);
    expect(Domaine::sole()->statut)->toBe('en_attente');

    Livewire::test(MonDomaine::class)->call('retirer')->assertRedirect(route('espace.formule'));
    expect(Domaine::count())->toBe(0);
});

it('oublie une declaration jamais prouvee apres le delai', function () {
    $domaine = Domaine::create(['user_id' => $this->creatif->id, 'nom' => 'jeanne-dupont.fr', 'statut' => 'a_verifier', 'source' => 'externe', 'jeton' => 'x']);
    $domaine->forceFill(['created_at' => now()->subDays(Domaine::DELAI_VERIFICATION + 1)])->save();
    dnsSimule();

    $this->artisan('ubdf:verifier-domaines');

    expect(Domaine::count())->toBe(0);
});

it('nomme l enregistrement TXT selon la marque du createur', function () {
    $this->creatif->update(['brand' => 'df']);
    Livewire::test(NomDeDomaine::class)->set('existant', 'jane-doe.com')->call('brancher');

    expect(Domaine::sole()->enregistrementTxt())
        ->nom->toBe('_dustfolio.jane-doe.com')
        ->valeur->toStartWith('dustfolio-verification=');
});
