<?php

use App\Services\Captcha\Captcha;


it('tire quatre caracteres, sans I, O ni L', function () {
    $captcha = new Captcha;

    foreach (range(1, 200) as $i) {
        expect($captcha->generer('contact'))->toMatch('/^[A-HJKMNP-Z0-9]{4}$/');
    }
});

it('verifie sans tenir compte de la casse ni des espaces, une seule fois', function () {
    $captcha = new Captcha;
    $code = $captcha->generer('contact');

    expect($captcha->verifier('contact', ' '.strtolower($code).' '))->toBeTrue()
        ->and($captcha->verifier('contact', $code))->toBeFalse();
});

it('oublie le code meme apres un echec', function () {
    $captcha = new Captcha;
    $code = $captcha->generer('contact');

    expect($captcha->verifier('contact', 'XXXX'))->toBeFalse()
        ->and($captcha->verifier('contact', $code))->toBeFalse();
});

it('garde un code distinct par formulaire', function () {
    $captcha = new Captcha;
    $contact = $captcha->generer('contact');
    $inscription = $captcha->generer('inscription');

    expect($captcha->verifier('inscription', $inscription))->toBeTrue()
        ->and($captcha->verifier('contact', $contact))->toBeTrue();
});

it('dessine le code en noir sur fond blanc hachure', function () {
    $svg = (new Captcha)->svg('AB12');

    expect($svg)->toContain('<pattern')->toContain('fill="#fff"')->toContain('fill="#000"')
        ->toContain('>A</text>')->toContain('>2</text>');
});
