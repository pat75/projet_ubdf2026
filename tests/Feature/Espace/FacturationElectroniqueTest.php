<?php

use App\Livewire\Espace\Compte;
use App\Models\BillingProfile;
use App\Models\User;
use App\Services\Facturation\AnnuaireEntreprises;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/*
 | Facturation electronique : le createur se declare professionnel, donne
 | son SIRET, et ses informations legales sont relevees aupres de
 | l'annuaire des entreprises de l'Etat plutot que saisies.
 */

/** Reponse type de recherche-entreprises.api.gouv.fr, reduite. */
function reponseAnnuaire(array $ecrase = []): array
{
    return ['results' => [array_replace([
        'siren' => '552081317',
        'nom_complet' => 'ELECTRICITE DE FRANCE (EDF)',
        'nom_raison_sociale' => 'ELECTRICITE DE FRANCE',
        'nature_juridique' => '5599',
        'date_creation' => '1955-01-01',
        'etat_administratif' => 'A',
        'tva' => ['FR03552081317'],
        'siege' => [
            'siret' => '55208131766522',
            'activite_principale' => '35.11Z',
            'adresse' => '22-30 22 AVENUE DE WAGRAM 75008 PARIS',
            'code_postal' => '75008',
            'libelle_commune' => 'PARIS',
            'etat_administratif' => 'A',
            'date_creation' => '2006-08-01',
        ],
        'matching_etablissements' => [],
    ], $ecrase)]];
}

beforeEach(function () {
    $this->creatif = User::factory()->create();
    cache()->flush();
});

it('valide la cle de Luhn avant d aller interroger l annuaire', function () {
    Http::fake();

    $annuaire = app(AnnuaireEntreprises::class);

    expect($annuaire->siretValide('55208131766522'))->toBeTrue()
        ->and($annuaire->siretValide('55208131766523'))->toBeFalse()
        ->and($annuaire->siretValide('552081317'))->toBeFalse();

    Http::assertNothingSent();
});

it('calcule le numero de TVA francais a partir du SIREN', function () {
    expect(app(AnnuaireEntreprises::class)->tvaIntracommunautaire('552081317'))->toBe('FR03552081317');
});

it('releve les informations d entreprise et les enregistre', function () {
    Http::fake(['recherche-entreprises.api.gouv.fr/*' => Http::response(reponseAnnuaire())]);

    Livewire::actingAs($this->creatif)->test(Compte::class)
        ->call('basculerProfessionnel')
        ->set('siret', '552 081 317 66522')
        ->call('verifierSiret')
        ->assertSet('erreurSiret', null);

    $profil = BillingProfile::where('user_id', $this->creatif->id)->sole();

    expect($profil->siret)->toBe('55208131766522')
        ->and($profil->siren)->toBe('552081317')
        ->and($profil->company_name)->toBe('ELECTRICITE DE FRANCE (EDF)')
        ->and($profil->vat_number)->toBe('FR03552081317')
        ->and($profil->naf_code)->toBe('35.11Z')
        ->and($profil->city)->toBe('PARIS')
        ->and($profil->formeJuridique())->toBe('Société anonyme (SA)')
        ->and($profil->actif())->toBeTrue()
        // La reponse complete est conservee : les champs de l'API bougent.
        ->and($profil->payload)->toHaveKey('siren');
});

it('refuse un SIRET mal forme sans appeler l annuaire', function () {
    Http::fake();

    Livewire::actingAs($this->creatif)->test(Compte::class)
        ->call('basculerProfessionnel')
        ->set('siret', '12345678901234')
        ->call('verifierSiret');

    expect(BillingProfile::count())->toBe(0);
    Http::assertNothingSent();
});

it('explique quand l annuaire ne connait pas le numero', function () {
    Http::fake(['recherche-entreprises.api.gouv.fr/*' => Http::response(['results' => []])]);

    $page = Livewire::actingAs($this->creatif)->test(Compte::class)
        ->call('basculerProfessionnel')
        ->set('siret', '55208131766522')
        ->call('verifierSiret');

    expect($page->get('erreurSiret'))->toContain('Aucune entreprise')
        ->and(BillingProfile::count())->toBe(0);
});

it('accepte un etablissement ferme mais le signale', function () {
    Http::fake(['recherche-entreprises.api.gouv.fr/*' => Http::response(reponseAnnuaire([
        'siege' => ['siret' => '55208131766522', 'etat_administratif' => 'C', 'libelle_commune' => 'PARIS'],
    ]))]);

    Livewire::actingAs($this->creatif)->test(Compte::class)
        ->call('basculerProfessionnel')
        ->set('siret', '55208131766522')
        ->call('verifierSiret');

    expect(BillingProfile::sole()->actif())->toBeFalse();
});

it('tient bon quand l annuaire ne repond pas', function () {
    Http::fake(['recherche-entreprises.api.gouv.fr/*' => Http::response('', 503)]);

    $page = Livewire::actingAs($this->creatif)->test(Compte::class)
        ->call('basculerProfessionnel')
        ->set('siret', '55208131766522')
        ->call('verifierSiret');

    expect($page->get('erreurSiret'))->toContain('ne répond pas')
        ->and(BillingProfile::count())->toBe(0);
});

it('oublie l entreprise quand le createur repasse en particulier', function () {
    Http::fake(['recherche-entreprises.api.gouv.fr/*' => Http::response(reponseAnnuaire())]);

    $page = Livewire::actingAs($this->creatif)->test(Compte::class)
        ->call('basculerProfessionnel')
        ->set('siret', '55208131766522')
        ->call('verifierSiret');

    expect(BillingProfile::count())->toBe(1);

    $page->call('basculerProfessionnel');

    expect(BillingProfile::count())->toBe(0);
});

it('affiche les informations relevees sur la fiche', function () {
    BillingProfile::create([
        'user_id' => $this->creatif->id,
        'siret' => '55208131766522', 'siren' => '552081317',
        'company_name' => 'ELECTRICITE DE FRANCE (EDF)', 'legal_form' => '5599',
        'naf_code' => '35.11Z', 'vat_number' => 'FR03552081317',
        'address' => '22 AVENUE DE WAGRAM', 'postcode' => '75008', 'city' => 'PARIS',
        'admin_state' => 'A', 'checked_at' => now(),
    ]);

    $this->actingAs($this->creatif)->get(route('espace.compte'))->assertOk()
        ->assertSee('Facturation électronique')
        ->assertSee('ELECTRICITE DE FRANCE (EDF)')
        // Le SIRET s'affiche par groupes, comme il se dicte.
        ->assertSee('552 081 317 66522')
        ->assertSee('FR03552081317')
        ->assertSee('Société anonyme (SA)');
});

/*
 | Envoi automatique : il n'y a pas de bouton « Vérifier ». Le numero part
 | des qu'il est complet et que sa cle est bonne.
 */

it('interroge l annuaire des que le SIRET saisi est valide', function () {
    Http::fake(['recherche-entreprises.api.gouv.fr/*' => Http::response(reponseAnnuaire())]);

    Livewire::actingAs($this->creatif)->test(Compte::class)
        ->call('basculerProfessionnel')
        ->set('siret', '552 081 317 66522')
        ->assertSet('erreurSiret', null);

    expect(BillingProfile::where('user_id', $this->creatif->id)->exists())->toBeTrue();
});

it('ne dit rien et n appelle personne tant que la saisie est incomplete', function () {
    Http::fake();

    Livewire::actingAs($this->creatif)->test(Compte::class)
        ->call('basculerProfessionnel')
        ->set('siret', '552 081')
        ->assertSet('erreurSiret', null);

    Http::assertNothingSent();
});
