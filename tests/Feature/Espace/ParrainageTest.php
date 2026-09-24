<?php

use App\Livewire\Espace\Parrainage;
use App\Models\PromoCode;
use App\Models\User;
use Livewire\Livewire;

function abonne(array $attrs = []): User
{
    return User::factory()->create($attrs + ['plan' => 1, 'plan_started_at' => now()->subMonth(), 'plan_months' => 12]);
}

it('donne le code au format du legacy', function () {
    $u = abonne(['login' => 'arnaudjouffroy', 'legacy_id' => 46969]);

    $this->actingAs($u)->get(route('espace.formule'))->assertSee('AR-46969');
});

it('credite parrain et filleul', function () {
    $parrain = abonne(['login' => 'arnaud', 'legacy_id' => 46969]);
    $filleul = abonne(['plan_months' => 6]);

    Livewire::actingAs($filleul)->test(Parrainage::class)->set('code', 'ar-46969')->call('utiliser')
        ->assertSee('1 mois offerts');

    expect($parrain->fresh()->plan_months)->toBe(15)
        ->and($filleul->fresh()->plan_months)->toBe(7);

    Livewire::actingAs($filleul)->test(Parrainage::class)->set('code', 'AR-46969')->call('utiliser')
        ->assertSee('déjà utilisé');
});

it('refuse le parrainage sans formule payante', function () {
    abonne(['login' => 'arnaud', 'legacy_id' => 46969]);
    $gratuit = User::factory()->create(['plan' => 0]);

    Livewire::actingAs($gratuit)->test(Parrainage::class)->set('code', 'AR-46969')->call('utiliser')
        ->assertSee('réservé aux formules payantes');
});

it('credite un code promo une seule fois', function () {
    PromoCode::create(['code' => 'UBKUIXB6', 'discount' => 6, 'discount_type' => PromoCode::MOIS, 'max_uses' => 1]);
    $u = User::factory()->create(['plan' => 0]);

    Livewire::actingAs($u)->test(Parrainage::class)->set('code', 'ubkuixb6')->call('utiliser')->assertSee('6 mois');
    expect($u->fresh())->plan->toBe(1)->plan_months->toBe(6);

    $autre = User::factory()->create();
    Livewire::actingAs($autre)->test(Parrainage::class)->set('code', 'UBKUIXB6')->call('utiliser')->assertSee('Code non valide');
});

it('freine les essais de codes', function () {
    $u = User::factory()->create();
    $c = Livewire::actingAs($u)->test(Parrainage::class);
    foreach (range(1, 5) as $i) {
        $c->set('code', 'FAUX'.$i)->call('utiliser');
    }

    $c->set('code', 'FAUX6')->call('utiliser')->assertSee('Trop d’essais');
});

/*
 | Presentation : le bloc d'activation est visible sur la page, le
 | parrainage est replie comme les factures. Un seul champ pour les deux
 | sortes de codes — le service les distingue a leur forme.
 */
it('montre le bloc d activation et replie le parrainage', function () {
    $creatif = User::factory()->create(['plan' => 1]);

    $reponse = $this->actingAs($creatif)->get(route('espace.formule'))->assertOk()
        ->assertSee('Activer un code formule')
        ->assertSee('Comment obtenir un code formule ?', false)
        ->assertSee('chat-code-formule.png')
        ->assertSee('Parrainage');

    /*
     | Le parrainage est dans un depliant : son titre est porte par le
     | bouton d'ouverture de x-espace.section-pliante, celui-la meme qui
     | sert aux factures. On le reconnait a son aria-expanded.
     */
    expect($reponse->getContent())
        ->toMatch('/:aria-expanded="ouvert".{0,400}Parrainage/s');
});
