<?php

use App\Support\LegacyText;

it('laisse intact un texte UTF-8 correct', function (string $value) {
    expect(LegacyText::clean($value))->toBe($value);
})->with([
    'Scénographe',
    'Éditions Été',
    'Illustration « jeunesse »',
    'Projet d&#039;accessoire',
    'ÃŽle',                       // contient un marqueur mais reste valide seul
    '日本語',
    '',
]);

it('preserve null', function () {
    expect(LegacyText::clean(null))->toBeNull();
});

it('retire l octet orphelin d un varchar tronque', function () {
    // Cas reel : ub2_gal_img.img_id = 477277, coupe au milieu d'un « à ».
    $truncated = 'motifs variés affiliés '.hex2bin('c3');

    $cleaned = LegacyText::clean($truncated);

    expect(mb_check_encoding($cleaned, 'UTF-8'))->toBeTrue()
        ->and($cleaned)->toBe('motifs variés affiliés ');
});

it('corrige un double encodage avere', function () {
    $doubleEncoded = mb_convert_encoding('Scénographe été', 'UTF-8', 'ISO-8859-1');

    expect($doubleEncoded)->not->toBe('Scénographe été')
        ->and(LegacyText::clean($doubleEncoded))->toBe('Scénographe été');
});

it('ne touche pas a un texte dont le re-decodage serait invalide', function () {
    // « Â » isole : marqueur present, mais re-decoder casserait la chaine.
    $value = 'Prix : 100Â';

    expect(LegacyText::clean($value))->toBe($value);
});
