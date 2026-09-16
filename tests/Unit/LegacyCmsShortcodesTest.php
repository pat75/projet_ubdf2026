<?php

use App\Services\Legacy\LegacyCms;

/** Appelle la methode privee de resolution des codes courts. */
function corps(string $html, string $titre = 'Test'): ?string
{
    $methode = new ReflectionMethod(LegacyCms::class, 'corps');

    return $methode->invoke(new LegacyCms, $html, $titre);
}

it('developpe le code court perso', function () {
    $html = corps('<p>[perso name="Patrice Tardif" img="/a.jpg" role="Fondateur"]</p>');

    expect($html)->toContain('ub_perso')
        ->toContain('Patrice Tardif')
        ->toContain('Fondateur')
        ->toContain('/a.jpg')
        ->not->toContain('[perso');
});

it('remplace une image manquante par celle du theme', function () {
    expect(corps('[perso name="Anonyme" img="" role=""]'))
        ->toContain('tea24_big_gris.gif');
});

it('developpe le code court clear', function () {
    expect(corps('a[clear]b'))->toBe('a<br clear="all"/>b');
});

it('retire ub_formule, qui n avait aucun gestionnaire dans le legacy', function () {
    // Les deux pages de tarifs affichaient le code court en toutes lettres.
    expect(corps('<p>[ub_formule]</p>', 'Les formules Ultra-book'))
        ->toBe('<p></p>')
        ->not->toContain('ub_formule');
});

it('laisse le HTML inchange quand il ne porte aucun code court', function () {
    expect(corps('<p>Texte <strong>simple</strong>.</p>'))
        ->toBe('<p>Texte <strong>simple</strong>.</p>');
});

it('rend null sur un contenu vide', function () {
    expect(corps(''))->toBeNull();
});
