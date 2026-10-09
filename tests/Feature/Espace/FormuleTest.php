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
        ->assertSee('Vous êtes en formule gratuite.')
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
        ->assertSee('Facturé 36,80 € en une seule fois')
        ->assertSee('Meilleure offre')
        // 21,90 € sur six mois.
        ->assertSee('3,65 €');
});

/*
 | Un createur qui a deja paye ne souscrit plus : il renouvelle. La page
 | le lui dit, et le badge de la carte vedette change de libelle.
 */
it('annonce le renouvellement a qui a deja paye une facture', function () {
    // Le createur du beforeEach a une facture payee.
    $this->get(route('espace.formule'))->assertOk()
        ->assertSee('Vos tarifs de renouvellement, réservés aux créatifs déjà abonnés.', false)
        ->assertSee('Offre renouvellement')
        ->assertDontSee('Meilleure offre');

    // Un nouveau venu garde « Meilleure offre ».
    $this->actingAs(User::factory()->create(['plan' => 0]))
        ->get(route('espace.formule'))->assertOk()
        ->assertSee('Meilleure offre')
        ->assertDontSee('Offre renouvellement');
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

it('place le parrainage a la suite des factures', function () {
    $this->get(route('espace.formule'))->assertOk()
        ->assertSeeInOrder(['Activer un code formule', 'Factures', 'Parrainage', 'Conditions générales de vente'], escape: false);
});

it('integre le login du book au sujet du mail de contact', function () {
    $login = $this->creatif->login;
    $reponse = $this->get(route('espace.formule'))->assertOk();

    // Le lien mailto contient le sujet encode avec le login du createur.
    expect($reponse->getContent())
        ->toContain('?subject=Question')
        ->toContain(urlencode($login));
});
