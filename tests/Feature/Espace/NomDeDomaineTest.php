<?php

use App\Livewire\Espace\MonDomaine;
use App\Livewire\Espace\NomDeDomaine;
use App\Models\Domaine;
use App\Models\User;
use App\Services\Paiement\PasserellePayplug;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

/*
 | Achat d'un nom de domaine depuis Ma formule (developpement seulement) :
 | recherche, titulaire, consentements, paiement Payplug, commande OVH
 | simulee (domaines.ovh_mode = simulation), page de gestion.
 */

beforeEach(function () {
    config(['domaines.ovh_mode' => 'simulation']);
    $this->creatif = User::factory()->create([
        'firstname' => 'Jeanne', 'lastname' => 'Dupont', 'email' => 'jeanne@exemple.fr',
        'mobile' => '06 12 34 56 78', 'address' => '1 rue X', 'zipcode' => '69001', 'city' => 'Lyon', 'country' => 'France',
    ]);
    $this->actingAs($this->creatif);
    $this->payplug = Mockery::mock(PasserellePayplug::class);
    app()->instance(PasserellePayplug::class, $this->payplug);
});

function paiementDomaine(Domaine $d, ?int $montant = null, string $id = 'pay_dom'): array
{
    return ['id' => $id, 'paye' => true, 'montant' => $montant ?? (int) round($d->prix_ttc * 100),
        'metadata' => ['customer_id' => $d->user_id, 'product_id' => 2, 'domaine_id' => $d->id], 'brut' => ['id' => $id, 'is_live' => false]];
}

function parcoursJusquAuPaiement(string $libelle = 'jeanne-dupont', string $extension = 'fr')
{
    return Livewire::test(NomDeDomaine::class)
        ->set('recherche', $libelle)->set('extension', $extension)->call('rechercher')
        ->call('choisir', $extension)
        ->set('cgu', true)->set('renonciation', true);
}

it('affiche l option sous les formules', function () {
    $this->get(route('espace.formule'))->assertOk()->assertSee('Réserver mon domaine')->assertSee('dev_only', false);
});

it('donne la disponibilite et le prix de l extension choisie', function () {
    Livewire::test(NomDeDomaine::class)
        ->set('recherche', 'Jeanne Dupont')->call('rechercher')
        ->assertSet('recherche', 'jeanne-dupont')
        ->assertSet('resultats', ['fr' => ['nom' => 'jeanne-dupont.fr', 'disponible' => true, 'prix' => 16.0]])
        ->assertSee('16,00 € TTC / an');

    Livewire::test(NomDeDomaine::class)->set('recherche', 'jeanne')->set('extension', 'com')->call('rechercher')
        ->assertSet('resultats.com.prix', 19.0)->assertSee('19,00 € TTC / an');

    Livewire::test(NomDeDomaine::class)->set('recherche', 'deja-pris')->call('rechercher')
        ->assertSet('resultats.fr.disponible', false)->assertSee('Déjà pris');
});

it('suit l extension tapee dans le nom', function () {
    Livewire::test(NomDeDomaine::class)->set('recherche', 'jeanne-dupont.com')->call('rechercher')
        ->assertSet('extension', 'com')
        ->assertSet('resultats.com.nom', 'jeanne-dupont.com');
});

it('refuse un nom mal forme', function (string $saisie) {
    Livewire::test(NomDeDomaine::class)->set('recherche', $saisie)->call('rechercher')->assertHasErrors('recherche');
})->with(['', '!!!', str_repeat('a', 64), 'ab--cd']);

it('pre-remplit le titulaire depuis Mon compte, aux formats OVH', function () {
    Livewire::test(NomDeDomaine::class)
        ->set('recherche', 'jeanne-dupont')->call('rechercher')->call('choisir', 'fr')
        ->assertSet('titulaire.prenom', 'Jeanne')
        ->assertSet('titulaire.telephone', '+33.612345678')
        ->assertSet('titulaire.pays', 'FR');
});

it('exige les CGU et la renonciation au droit de retractation', function () {
    parcoursJusquAuPaiement()->set('cgu', false)->set('renonciation', false)
        ->call('payer')->assertHasErrors(['cgu', 'renonciation']);

    expect(Domaine::count())->toBe(0);
});

it('reserve un .fr aux titulaires de l Union europeenne', function () {
    $this->payplug->shouldReceive('creerPaiement')->andReturn(['id' => 'pay_dom', 'url' => 'https://x']);
    parcoursJusquAuPaiement()->set('titulaire.pays', 'CH')->call('payer')->assertHasErrors('titulaire.pays');
    parcoursJusquAuPaiement('jeanne-dupont', 'com')->set('titulaire.pays', 'CH')->call('payer')->assertHasNoErrors('titulaire.pays');
});

it('enregistre les preuves de consentement et envoie vers Payplug', function () {
    $this->payplug->shouldReceive('creerPaiement')->once()
        ->withArgs(fn ($d) => $d['amount'] === 1600 && $d['metadata']['product_id'] === 2)
        ->andReturn(['id' => 'pay_dom', 'url' => 'https://secure.payplug.com/pay/x']);

    parcoursJusquAuPaiement()->call('payer')->assertDispatched('payplug-ouvrir', url: 'https://secure.payplug.com/pay/x');

    expect(Domaine::sole())
        ->statut->toBe('a_payer')
        ->payplug_id->toBe('pay_dom')
        ->cgu_acceptees_le->not->toBeNull()
        ->renonciation_le->not->toBeNull()
        ->consentement_version->toBe(config('domaines.version_consentement'))
        ->and(Domaine::sole()->titulaire['nom'])->toBe('Dupont');
});

it('facture puis commande le domaine a la notification Payplug, une seule fois', function () {
    $this->payplug->shouldReceive('creerPaiement')->andReturn(['id' => 'pay_dom', 'url' => 'https://x']);
    parcoursJusquAuPaiement()->call('payer');
    $domaine = Domaine::sole();
    $this->payplug->shouldReceive('lireNotification')->twice()->andReturn(paiementDomaine($domaine));

    $this->post(route('payplug.notification'))->assertOk();
    $this->post(route('payplug.notification'))->assertOk();

    expect($domaine->fresh())
        ->statut->toBe('commande')
        ->ovh_commande_id->toStartWith('simulation-')
        ->expire_le->not->toBeNull()
        ->and($this->creatif->invoices()->sole()->designation)->toBe('Nom de domaine jeanne-dupont.fr (1 an)');
});

it('confirme le paiement au retour du createur, en le relisant chez Payplug', function () {
    $this->payplug->shouldReceive('creerPaiement')->andReturn(['id' => 'pay_dom', 'url' => 'https://x']);
    parcoursJusquAuPaiement()->call('payer');
    $this->payplug->shouldReceive('lirePaiement')->with('pay_dom')->once()->andReturn(paiementDomaine(Domaine::sole()));

    $this->get(route('espace.formule', ['domaine' => 'retour']))->assertOk()->assertSee('Gérer mon domaine')
        ->assertSee('Votre domaine vient d’être réservé')->assertSee('thank-you.png')
        ->assertSee(route('espace.domaine'));
});

it('ignore un paiement au mauvais montant', function () {
    $this->payplug->shouldReceive('creerPaiement')->andReturn(['id' => 'pay_dom', 'url' => 'https://x']);
    parcoursJusquAuPaiement()->call('payer');
    $this->payplug->shouldReceive('lireNotification')->andReturn(paiementDomaine(Domaine::sole(), 100));

    $this->post(route('payplug.notification'));

    expect(Domaine::sole()->statut)->toBe('a_payer')->and($this->creatif->invoices()->count())->toBe(0);
});

it('rembourse le createur si OVH refuse la commande', function () {
    Mail::fake();
    $this->payplug->shouldReceive('creerPaiement')->andReturn(['id' => 'pay_dom', 'url' => 'https://x']);
    parcoursJusquAuPaiement('commande-echec')->call('payer');
    $this->payplug->shouldReceive('lireNotification')->andReturn(paiementDomaine(Domaine::sole()));
    $this->payplug->shouldReceive('rembourser')->once()->with('pay_dom');

    $this->post(route('payplug.notification'));

    expect(Domaine::sole()->statut)->toBe('rembourse');
});

it('affiche la page du domaine et le menu, puis resilie et transfere', function () {
    $domaine = Domaine::create([
        'user_id' => $this->creatif->id, 'nom' => 'jeanne-dupont.fr', 'extension' => 'fr', 'prix_ttc' => 16,
        'statut' => 'actif', 'source' => 'ovh', 'titulaire' => ['prenom' => 'Jeanne', 'nom' => 'Dupont'], 'expire_le' => now()->addYear(),
    ]);

    $this->get(route('espace.domaine'))->assertOk()->assertSee('jeanne-dupont.fr')->assertSee('Nom de domaine');

    Livewire::test(MonDomaine::class)->set('confirmation', 'oui')->call('resilier')->assertHasErrors('confirmation');
    expect(Domaine::sole()->resilie_le)->toBeNull();
    Livewire::test(MonDomaine::class)->set('confirmation', ' Résilier ')->call('resilier')->assertSee('Résilié : actif jusqu’à l’échéance');
    expect($domaine->fresh())->statut->toBe('resilie')->resilie_le->not->toBeNull();

    Livewire::test(MonDomaine::class)->call('transferer')->assertSee('SIMU-');
    expect($domaine->fresh()->statut)->toBe('transfert');
});

it('ne montre pas la page du domaine d un autre createur', function () {
    Domaine::create(['user_id' => User::factory()->create()->id, 'nom' => 'autre.fr', 'statut' => 'actif']);

    $this->get(route('espace.domaine'))->assertNotFound();
});

it('n existe pas en production', function () {
    app()->detectEnvironment(fn () => 'production');
    Domaine::create(['user_id' => $this->creatif->id, 'nom' => 'jeanne-dupont.fr', 'statut' => 'actif']);

    expect(Domaine::achatOuvert())->toBeFalse();
    $this->get(route('espace.domaine'))->assertNotFound();
});
