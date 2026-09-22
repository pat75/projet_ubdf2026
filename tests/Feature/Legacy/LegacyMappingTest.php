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

it('refuse toute ecriture sur la connexion WordPress', function () {
    DB::connection('legacy_wp')->statement('UPDATE wp_posts SET post_title = post_title');
})->throws(RuntimeException::class, 'Ecriture interdite');

it('lit le WordPress du magazine en UTF-8, sans double encodage', function () {
    // Contrairement a ub2020, cette base est declaree utf8 et contient
    // reellement de l'UTF-8 : la lire en latin1 l'abimerait.
    $titre = DB::connection('legacy_wp')->table('posts')->where('ID', 16)->value('post_title');

    expect($titre)->toBe('Écoles partenaires');
});

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

it('distingue le classique de 2010 du classique 2015', function () {
    // Le legacy rend « Modele classique » avec ultrabook_type.tlp.php, le
    // theme de 2010, et `mdl_2015_classique` avec classique2015/. Les
    // confondre changeait l'apparence de 5 295 books actifs.
    $map = config('categories.legacy_theme_map');

    expect($map['modèle classique'])->toBe('mdl_classique')
        ->and($map['mdl_classique'])->toBe('mdl_classique')
        ->and($map['mdl_2015_classique'])->toBe('mdl_2015_classique');
});

it('convertit le theme par defaut comme le legacy a l affichage', function () {
    // 2011_front/action_book.php : mdl_default et vide deviennent 2014-Responsive.
    expect(config('categories.legacy_theme_map.mdl_default'))->toBe('mdl_2014_responsive')
        ->and(config('categories.legacy_theme_map')[''])->toBe('mdl_2014_responsive');
});

it('ne referme aucun slug de legacy_map hors des categories declarees', function () {
    $slugs = collect(config('categories.list'))->pluck('slug');

    expect(collect(config('categories.legacy_map'))->values()->unique()->diff($slugs))->toBeEmpty();
});

/*
 * Audit d'encodage rejoue a chaque execution : si une valeur du legacy sort
 * du profil mesure le 2026-09-15, la migration de donnees doit etre revue
 * avant d'etre relancee.
 */
it('ne contient aucune valeur non UTF-8 hors les deux troncatures connues', function () {
    $columns = [
        'inc_user' => ['us_nom', 'us_prenom', 'us_ville'],
        'inc_user_pref' => ['us_pf_nom', 'us_pf_descp', 'us_pf_piedpage'],
        'ub2_gal_rub' => ['rub_nom'],
        'ub2_gal_img' => ['img_titre', 'img_desc'],
    ];

    $invalid = 0;

    foreach ($columns as $table => $cols) {
        foreach ($cols as $column) {
            DB::connection('legacy')->table($table)
                ->select($column)->whereNotNull($column)->where($column, '<>', '')
                ->orderBy(DB::raw(1))
                ->chunk(50000, function ($rows) use ($column, &$invalid) {
                    foreach ($rows as $row) {
                        if (! mb_check_encoding($row->{$column}, 'UTF-8')) {
                            $invalid++;
                        }
                    }
                });
        }
    }

    // Les deux seules anomalies connues : ub2_gal_img 477277 et 1414387.
    expect($invalid)->toBeLessThanOrEqual(2);
})->group('slow');

it('ne contient aucun double encodage au niveau des octets', function () {
    $columns = [
        'inc_user' => ['us_nom', 'us_ville'],
        'inc_user_pref' => ['us_pf_descp'],
        'ub2_gal_rub' => ['rub_nom'],
        'ub2_gal_img' => ['img_titre', 'img_desc'],
    ];

    foreach ($columns as $table => $cols) {
        foreach ($cols as $column) {
            $count = DB::connection('legacy')->table($table)
                ->whereRaw("HEX({$column}) LIKE '%C383C2%'")
                ->orWhereRaw("HEX({$column}) LIKE '%C383C3%'")
                ->orWhereRaw("HEX({$column}) LIKE '%C3A2C280%'")
                ->count();

            expect($count)->toBe(0, "{$table}.{$column}");
        }
    }
})->group('slow');
