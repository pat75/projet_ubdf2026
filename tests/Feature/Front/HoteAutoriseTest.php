<?php

/*
 | Le joker DNS envoie n'importe quel hote : seuls le portail (avec ou sans
 | www) et les books a un niveau sont servis, le reste va au portail.
 */

function hoteBooks(): string
{
    return config('ubdf.book_domain');
}

it('redirige un hote a deux niveaux vers le portail, chemin conserve', function () {
    $this->get('https://www.remipepin.'.hoteBooks().'/illustrateur?page=2')
        ->assertStatus(301)
        ->assertRedirect('https://'.config('marques.marques.ub.hotes')[0].'/illustrateur?page=2');
});

it('sert le portail avec et sans www', function (string $prefixe) {
    $this->get('https://'.$prefixe.hoteBooks().'/')->assertOk();
})->with(['', 'www.']);

it('laisse passer un book a un niveau', function () {
    expect($this->get('https://pat10.'.hoteBooks().'/')->status())->not->toBe(301);
});

it('laisse passer le portail Dustfolio', function () {
    expect($this->get('https://'.config('marques.marques.df.hotes')[0].'/')->status())->not->toBe(301);
});

it('ne redirige jamais la notification Payplug', function () {
    expect($this->post('https://www.intrus.'.hoteBooks().'/payplug/notification')->status())->not->toBe(301);
});
