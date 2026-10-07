<?php

use App\Services\Legacy\LegacyLogins;

it('accepte en source les logins qui commencent ou finissent par _ ou -', function () {
    foreach (['_miette', '-sophie-', '-seb-_desactive', '_margot_', 'a_menguy'] as $login) {
        expect(preg_match('/'.LegacyLogins::SOURCE.'/', $login))->toBe(1, $login);
    }
    expect(preg_match('/'.LegacyLogins::SOURCE.'/', 'a'))->toBe(0);
});

it('retire les separateurs de tete et de queue', function () {
    $logins = new LegacyLogins(['_miette', '-sophie-', '_kichi_desactive']);

    expect($logins->nouveau('_miette'))->toBe('miette')
        ->and($logins->nouveau('-sophie-'))->toBe('sophie')
        ->and($logins->nouveau('_kichi_desactive'))->toBe('kichi-desactive')
        ->and($logins->ecartes())->toBe([]);
});

it('ecarte un login dont la forme nettoyee est deja prise', function () {
    $logins = new LegacyLogins(['mimibelle', 'mimibelle_', '_mimibelle_']);

    expect($logins->nouveau('mimibelle_'))->toBeNull()
        ->and($logins->nouveau('_mimibelle_'))->toBeNull()
        ->and($logins->ecartes())->toBe(['mimibelle_', '_mimibelle_']);
});
