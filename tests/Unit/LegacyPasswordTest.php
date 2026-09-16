<?php

use App\Support\LegacyPassword;

it('dechiffre le format Rijndael-256 ECB', function () {
    // Valeurs reelles de ub2020.inc_user.us_pass (encryptClass::encode).
    expect(LegacyPassword::decrypt('/JvnG4Rj//SgoxdTGDiCSCejepTxhWKFGYVGH/ze/Pk='))->toBe('ub20192019')
        ->and(LegacyPassword::decrypt('WiTMrxsa8cE/yTUg9mZH/+qcKVAtIrjBaVNX84ZZdB4='))->toBe('shtafe');
});

it('dechiffre le format AES-256-CBC', function () {
    // encryptClass::encode_2, introduit au passage a PHP 7.2.
    expect(LegacyPassword::decrypt('cC91aFJ2QS9xQkNnM0RmNU1qRHhQdz09Ojrp8nac6KEZ/uAMPz1eSDz9'))->toBe('hpets');
});

it('rend null sur une valeur vide ou illisible', function (?string $value) {
    expect(LegacyPassword::decrypt($value))->toBeNull();
})->with([null, '', '   ', 'pas du base64 !', 'YWJj']);
