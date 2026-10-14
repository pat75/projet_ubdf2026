<?php

use App\Models\Domaine;
use App\Models\User;
use App\Services\Domaines\Ovh;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['services.ovh' => [
        'endpoint' => 'https://eu.api.ovh.com/1.0',
        'application_key' => 'ak', 'application_secret' => 'as', 'consumer_key' => 'ck',
        'filiale' => 'FR', 'ip_vps' => '203.0.113.10', 'ip6_vps' => null,
    ]]);
    $this->domaine = Domaine::create(['user_id' => User::factory()->create()->id, 'nom' => 'jeanne-dupont.fr']);
});

it('signe chaque requete selon la regle OVH', function () {
    Http::fake(['*/order/cart' => Http::response(['cartId' => 'c1']), '*' => Http::response([])]);

    app(Ovh::class)->disponibilite('jeanne-dupont.fr');

    Http::assertSent(function (Request $r) {
        $attendu = '$1$'.sha1(implode('+', ['as', 'ck', $r->method(), $r->url(), $r->body(), $r->header('X-Ovh-Timestamp')[0]]));

        return $r->url() === 'https://eu.api.ovh.com/1.0/order/cart'
            && $r->header('X-Ovh-Application')[0] === 'ak'
            && $r->header('X-Ovh-Signature')[0] === $attendu;
    });
});

it('lit la disponibilite et le prix', function () {
    Http::fake([
        '*/order/cart' => Http::response(['cartId' => 'c1']),
        '*/order/cart/c1/assign' => Http::response(null),
        '*/order/cart/c1/domain*' => Http::response([[
            'action' => 'create', 'orderable' => true,
            'prices' => [['label' => 'TOTAL', 'price' => ['text' => '8.39 €']]],
        ]]),
    ]);

    expect(app(Ovh::class)->disponibilite('Jeanne-Dupont.fr'))->toBe(['disponible' => true, 'prix' => '8.39 €']);
});

it('commande le domaine au nom du createur', function () {
    Http::fake([
        '*/me/contact' => Http::response(['id' => 77]),
        '*/order/cart' => Http::response(['cartId' => 'c1']),
        '*/order/cart/c1/assign' => Http::response(null),
        '*/order/cart/c1/domain' => Http::response(['itemId' => 5]),
        '*/order/cart/c1/item/5/configuration' => Http::response([]),
        '*/order/cart/c1/checkout' => Http::response(['orderId' => 123456]),
    ]);

    app(Ovh::class)->commander($this->domaine, [
        'prenom' => 'Jeanne', 'nom' => 'Dupont', 'email' => 'j@exemple.fr', 'telephone' => '+33.600000000',
        'adresse' => '1 rue X', 'code_postal' => '69001', 'ville' => 'Lyon', 'pays' => 'fr',
    ]);

    expect($this->domaine->fresh())->ovh_commande_id->toBe('123456')->source->toBe('ovh');
    Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/item/5/configuration')
        && $r['label'] === 'OWNER_CONTACT' && $r['value'] === '/me/contact/77');
});

it('pointe le domaine et www vers le VPS', function () {
    Http::fake(['*' => Http::response([])]);

    app(Ovh::class)->pointerVersVps($this->domaine);

    Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/domain/zone/jeanne-dupont.fr/record') && $r['subDomain'] === 'www' && $r['target'] === '203.0.113.10');
    Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/domain/zone/jeanne-dupont.fr/refresh'));
});

it('refuse de travailler sans cles', function () {
    config(['services.ovh.consumer_key' => null]);

    app(Ovh::class)->disponibilite('x.fr');
})->throws(RuntimeException::class, 'Cles OVH non configurees.');

it('remonte l erreur d OVH', function () {
    Http::fake(['*' => Http::response(['message' => 'This call has not been granted'], 403)]);

    app(Ovh::class)->disponibilite('x.fr');
})->throws(RuntimeException::class, 'This call has not been granted');

it('active un domaine dont le DNS pointe vers le VPS, et pointe un domaine OVH livre', function () {
    Http::fake(['*' => Http::response([])]);
    $ovh = Domaine::create(['user_id' => $this->domaine->user_id, 'nom' => 'achete.fr', 'source' => 'ovh']);
    $this->mock(App\Services\Domaines\Dns::class)->shouldReceive('ipv4')->andReturnUsing(
        fn (string $nom) => $nom === 'jeanne-dupont.fr' ? ['203.0.113.10'] : ['198.51.100.1']
    );

    $this->artisan('ubdf:verifier-domaines')->assertSuccessful();

    expect($this->domaine->fresh()->statut)->toBe('actif')
        ->and($ovh->fresh()->statut)->toBe('en_attente');
    Http::assertSent(fn (Illuminate\Http\Client\Request $r) => str_ends_with($r->url(), '/domain/zone/achete.fr/record'));
});
