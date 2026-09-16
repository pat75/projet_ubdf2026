<?php

use App\Support\MotsCles;

/**
 * Les cas ci-dessous sont tous releves dans ub2020 : la colonne de mots-cles
 * a ete remplie par des formulaires successifs qui n'encodaient pas la meme
 * chose, et la recherche doit donner le meme resultat quelle que soit
 * l'annee de saisie.
 */
it('decoupe une liste simple', function () {
    expect(MotsCles::decouper('illustration jeunesse, aquarelle, presse'))
        ->toBe(['illustration jeunesse', 'aquarelle', 'presse']);
});

it('deballe un tableau JSON echappe', function () {
    expect(MotsCles::decouper('[&#34; architecture&#34;,&#34; décoration&#34;]'))
        ->toBe(['architecture', 'décoration']);
});

it('retire les hashtags', function () {
    expect(MotsCles::decouper('#fashion, #chanel, #nyc'))
        ->toBe(['fashion', 'chanel', 'nyc']);
});

it('extrait le contenu d une balise meta collee dans le champ', function () {
    $brut = '&lt;meta name=&quot;keywords&quot; content=&quot; illustration, crayons&quot;/&gt;';

    expect(MotsCles::decouper($brut))->toBe(['illustration', 'crayons']);
});

it('accepte la virgule ideographique', function () {
    expect(MotsCles::decouper('webdesigner，平面设计师'))
        ->toBe(['webdesigner', '平面设计师']);
});

it('separe aussi sur le point-virgule, qui isole la traduction anglaise', function () {
    // js_core_pages.js ajoute la traduction du mot-cle apres un « ; ».
    expect(MotsCles::decouper('illustration;drawing'))->toBe(['illustration', 'drawing']);
});

it('dedoublonne sans tenir compte de la casse ni des accents', function () {
    expect(MotsCles::decouper('Illustration, illustration, ILLUSTRATION'))
        ->toBe(['Illustration']);
});

it('conserve l apostrophe, qui appartient au mot-cle', function () {
    expect(MotsCles::decouper("vue d'ensemble"))->toBe(["vue d'ensemble"]);
});

it('ignore une valeur vide ou reduite au tableau vide du formulaire', function () {
    expect(MotsCles::decouper('[]'))->toBe([])
        ->and(MotsCles::decouper(null))->toBe([])
        ->and(MotsCles::normaliser('  '))->toBeNull();
});

it('borne le nombre de mots-cles', function () {
    $brut = implode(',', array_map(fn ($i) => "mot{$i}", range(1, 80)));

    expect(MotsCles::decouper($brut))->toHaveCount(40);
});
