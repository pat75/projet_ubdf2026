<?php

use Illuminate\Support\Facades\DB;

/*
 * La base ub2020 est une source de verite figee : ces tests verrouillent
 * l'exhaustivite des tables de correspondance avant la migration des donnees.
 */

it('lit la base legacy avec le bon encodage', function () {
    $value = DB::connection('legacy')->table('inc_user')
        ->where('us_type', 'LIKE', 'Sc%nographe')
        ->value('us_type');

    expect($value)->toBe('Scénographe');
});

it('refuse toute ecriture sur la connexion legacy', function () {
    DB::connection('legacy')->statement('UPDATE inc_user SET us_nom = us_nom');
})->throws(RuntimeException::class, 'Ecriture interdite');

it('mappe toutes les valeurs de us_type vers une categorie', function () {
    $map = config('categories.legacy_map');

    $unmapped = DB::connection('legacy')->table('inc_user')
        ->whereNotNull('us_type')->where('us_type', '<>', '')
        ->distinct()->pluck('us_type')
        ->reject(fn (string $type) => isset($map[mb_strtolower($type)]))
        ->values()->all();

    expect($unmapped)->toBeEmpty();
});

it('mappe toutes les valeurs de theme vers un modele connu', function () {
    $map = config('categories.legacy_theme_map');

    $unmapped = DB::connection('legacy')->table('inc_user_pref')
        ->distinct()->pluck('us_pf_version_web')
        ->reject(fn (?string $theme) => isset($map[mb_strtolower((string) $theme)]))
        ->values()->all();

    expect($unmapped)->toBeEmpty();
});

it('ne referme aucun slug de legacy_map hors des categories declarees', function () {
    $slugs = collect(config('categories.list'))->pluck('slug');

    expect(collect(config('categories.legacy_map'))->values()->unique()->diff($slugs))->toBeEmpty();
});
