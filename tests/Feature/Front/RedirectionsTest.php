<?php

use App\Models\CmsPost;
use App\Models\User;

it('redirige les anciens raccourcis vers leur equivalent', function (string $ancienne, string $nouvelle) {
    $this->get($ancienne)->assertRedirect($nouvelle);
})->with([
    ['/formules', '/espace/formule'],
    ['/formules_dustfolio', '/espace/formule'],
    ['/messages', '/espace/messages'],
    ['/create', '/inscription'],
    ['/login', '/connexion'],
    ['/memobook', '/accueil'],
    ['/newsletters', '/actus'],
    ['/sitemap', '/sitemap.xml'],
    ['/ubaction__offres__4021', '/accueil'],
    ['/paypal_send_ultra-book_n__12_1', '/espace/formule'],
]);

it('renvoie une facture du legacy vers la facture reprise', function () {
    $creatif = User::factory()->create();
    $facture = $creatif->invoices()->create([
        'legacy_id' => 6836, 'number' => 'ub-6836', 'brand' => 'ub', 'amount' => 36.80, 'vat' => 6.13,
        'status' => 'paid', 'issued_at' => now(),
    ]);

    $this->get('/facture_n__6836')->assertRedirect('/espace/factures/'.$facture->id);
    $this->get('/invoice_n__6836')->assertRedirect('/espace/factures/'.$facture->id);

    // Facture inconnue : la liste, pas une 404.
    $this->get('/facture_n__999999')->assertRedirect('/espace/formule');
});

it('renvoie les anciennes adresses de book vers le sous-domaine', function () {
    $creatif = User::factory()->create(['login' => 'ariane']);

    $this->get('/book_ariane')->assertRedirect($creatif->bookUrl());
    $this->get('/minibook_ariane')->assertRedirect($creatif->bookUrl());
    $this->get('/-ariane')->assertRedirect($creatif->bookUrl());
    $this->get('/book_inconnu')->assertRedirect('/accueil');
});

it('renvoie un article du magazine vers son adresse actuelle', function () {
    $article = CmsPost::create(['legacy_id' => 16, 'slug' => 'ecoles-partenaires', 'locale' => 'fr', 'title' => 'Écoles partenaires']);

    $this->get('/ecoles__wpactu_16')->assertRedirect('/actus/'.$article->slug);
    $this->get('/inconnu__wpactu_999')->assertRedirect('/actus');
});

it('explique qu un ancien lien de conversation n est plus valable', function () {
    $this->get('/intermediate_msg_/custc5/'.str_repeat('a', 64).'/'.str_repeat('b', 16))
        ->assertRedirect('/accueil')
        ->assertSessionHas('statut');
});

it('sert les deux formes de l URL de defilement du legacy', function () {
    $this->getJson('/accueil__2__sel__all')->assertOk();
    $this->getJson('/accueil__sel__all__2')->assertOk();
});
