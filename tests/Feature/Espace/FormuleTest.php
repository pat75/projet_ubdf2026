<?php

use App\Models\User;

beforeEach(function () {
    $this->creatif = User::factory()->create(['plan' => 1, 'plan_started_at' => now()->subMonths(2), 'plan_months' => 12, 'lastname' => 'Martin']);
    $this->actingAs($this->creatif);
    $this->facture = $this->creatif->invoices()->create([
        'legacy_id' => 7907, 'number' => 'ub-7907', 'brand' => 'ub', 'label' => 'Ultra-book', 'designation' => 'Formule Ultra-book 12 mois',
        'amount' => 29.80, 'vat' => 4.97, 'status' => 'paid', 'issued_at' => '2020-11-02 10:00:00',
    ]);
});

it('affiche la formule, son echeance et les factures payees', function () {
    $this->creatif->invoices()->create(['number' => 'ub-x', 'brand' => 'ub', 'label' => 'Annulee', 'amount' => 1, 'vat' => 0, 'status' => 'cancelled', 'issued_at' => now()]);

    $this->get(route('espace.formule'))->assertOk()
        // L'echeance est datee en toutes lettres, comme sur la maquette :
        // « Renouvelable jusqu'au 10 mars 2034 ».
        ->assertSee(now()->subMonths(2)->addMonths(12)->translatedFormat('j F Y'))
        ->assertSee('UB-2020-7907')
        ->assertDontSee('Annulee');
});

it('affiche la facture au format du legacy', function () {
    $this->get(route('espace.facture', $this->facture))->assertOk()
        ->assertSee('Facture n° UB-2020-7907', false)
        ->assertSee('Formule Ultra-book')
        ->assertSee('24,83 € HT', false)
        ->assertSee('Martin')
        ->assertSee('FR31479054553');
});

it('refuse la facture d un autre creatif', function () {
    $this->actingAs(User::factory()->create())->get(route('espace.facture', $this->facture))->assertForbidden();
});

it('remercie le createur en formule payante et lui donne son code de diffusion', function () {
    $attendu = strtoupper(substr(hash_hmac('sha256', $this->creatif->login.date('Y'), config('services.diffusion.cle')), 0, 8));

    $this->get(route('espace.formule'))->assertOk()
        ->assertSeeInOrder(['Merci', 'pour votre soutien', 'Vous êtes actuellement en formule Premium'], escape: false)
        ->assertSee('Offre couplée')
        // Le code se recalcule des deux cotes : rien n'est stocke.
        ->assertSee($attendu);
});

it('affiche la formule gratuite sans le remerciement', function () {
    $gratuit = App\Models\User::factory()->create(['plan' => 0]);

    $this->actingAs($gratuit)->get(route('espace.formule'))->assertOk()
        ->assertSee('Vous êtes en formule gratuite')
        ->assertDontSee('pour votre soutien');
});

/*
 | La grille reprend la presentation de la page tarifs des Illustrateurs :
 | prix ramene au mois, total facture en une fois, et le douze mois mis en
 | avant comme meilleure offre.
 */
it('presente les offres au mois et met le douze mois en avant', function () {
    $creatif = User::factory()->create(['plan' => 0]);

    $this->actingAs($creatif)->get(route('espace.formule'))
        ->assertOk()
        // 36,80 € sur douze mois.
        ->assertSee('3,07 €')
        ->assertSee('Facturé 36,80 € en une fois')
        ->assertSee('Meilleur prix')
        // 21,90 € sur six mois.
        ->assertSee('3,65 €');
});

/*
 | Un createur qui a deja paye ne souscrit plus : il renouvelle. La page
 | le lui dit, et la carte vedette porte la mention « Réabonnement ».
 */
it('annonce le renouvellement a qui a deja paye une facture', function () {
    // Le createur du beforeEach a une facture payee.
    $this->get(route('espace.formule'))->assertOk()
        ->assertSee('Tarifs de renouvellement réservés aux créatifs déjà abonnés.', false)
        ->assertSee('Réabonnement · 12 mois');

    // Un nouveau venu voit le douze mois classique.
    $this->actingAs(User::factory()->create(['plan' => 0]))
        ->get(route('espace.formule'))->assertOk()
        ->assertDontSee('Réabonnement · 12 mois');
});

it('intitule les boutons de la grille « Sélectionner »', function () {
    $this->get(route('espace.formule'))->assertOk()
        ->assertSee('Sélectionner', false)
        ->assertDontSee('Payer par carte');
});

/*
 | Le bouton de la carte d'etat ne charge rien : il descend a la grille
 | des offres, plus bas sur la meme page.
 */
it('renvoie le bouton de la carte vers la grille des offres', function () {
    $reponse = $this->get(route('espace.formule'))->assertOk()
        ->assertSee('Renouveler')
        ->assertDontSee('Comparer les formules');

    expect($reponse->getContent())
        ->toContain('href="#offres"')
        ->toContain('id="offres"');
});

it('range le parrainage dans le bloc d activation, replie', function () {
    $this->get(route('espace.formule'))->assertOk()
        ->assertSeeInOrder(['Activer un code formule ou de parrainage', 'Mon code de parrainage', 'Factures', 'Conditions générales de vente'], escape: false)
        ->assertDontSee('Il s’active dans le champ ci-dessus.', false);
});

it('renvoie le bouton de contact vers le support', function () {
    $this->get(route('espace.formule'))->assertOk()
        ->assertSee(lien('support'), false);
});

it('masque le bloc de remerciement jusqu a la prochaine formule', function () {
    $this->creatif->update(['plan' => 1, 'plan_started_at' => now()->subDay(), 'plan_months' => 6]);
    $this->get(route('espace.formule'))->assertSee('Félicitations !');

    $this->post(route('espace.formule.merci.masquer'))->assertNoContent();
    $this->get(route('espace.formule'))->assertDontSee('Félicitations !');

    // Une nouvelle formule demarre : le remerciement revient.
    $this->creatif->update(['plan_started_at' => now()->addMinute()]);
    $this->get(route('espace.formule'))->assertSee('Félicitations !');
});

it('retient le repli du bloc offre couplee', function () {
    $this->creatif->update(['plan' => 1]);

    $this->postJson(route('espace.formule.replier', 'offre'), ['repliee' => true])->assertNoContent();
    expect($this->creatif->fresh()->offre_repliee)->toBeTrue();
    $this->get(route('espace.formule'))->assertSee('repliee: true', false);

    $this->postJson(route('espace.formule.replier', 'offre'), ['repliee' => false])->assertNoContent();
    expect($this->creatif->fresh()->offre_repliee)->toBeFalse();
});

it('retient le repli du bloc domaine et refuse un bloc inconnu', function () {
    $this->postJson(route('espace.formule.replier', 'domaine'), ['repliee' => true])->assertNoContent();
    expect($this->creatif->fresh()->domaine_replie)->toBeTrue();

    expect($this->postJson('/espace/formule/replier/password', ['repliee' => true])->isClientError())->toBeTrue();
});
