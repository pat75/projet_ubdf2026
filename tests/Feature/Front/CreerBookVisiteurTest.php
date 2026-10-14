<?php

use App\Models\Reglage;

it('propose le compte visiteur au retour de Google', function () {
    if (! Reglage::googleActif()) {
        $this->markTestSkipped('Google inactif');
    }

    $this->withSession(['google.inscription' => ['id' => 'g1', 'email' => 'a@b.fr', 'nom' => 'A', 'avatar' => null]])
        ->get(lien('inscription.page'))
        ->assertOk()
        ->assertSee('Créer un compte visiteur')
        ->assertSee(route('google.visiteur'), false);
});
