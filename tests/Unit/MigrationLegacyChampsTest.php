<?php

/*
| Garde-fou : un champ passe a updateOrCreate / create par le migrateur,
| mais absent du $fillable du modele, est ignore sans erreur. C'est ainsi
| que les dates d'inscription, de galerie et de media, et la confirmation
| des mails, ont ete perdues a la reprise. Ces champs-la doivent passer par
| forceFill().
*/

it('ne transmet au migrateur legacy que des champs remplissables', function () {
    $source = file_get_contents(app_path('Services/Legacy/LegacyMigrator.php'));
    $ignores = [];

    preg_match_all('/(\w+)::(?:updateOrCreate|create|firstOrCreate)\(/', $source, $appels, PREG_OFFSET_CAPTURE);

    foreach ($appels[1] as $i => [$modele]) {
        // Arguments de l'appel : jusqu'a la parenthese fermante correspondante.
        $debut = $appels[0][$i][1] + strlen($appels[0][$i][0]);
        for ($fin = $debut, $profondeur = 1; $profondeur > 0; $fin++) {
            $profondeur += ['(' => 1, ')' => -1][$source[$fin]] ?? 0;
        }
        $arguments = substr($source, $debut, $fin - $debut - 1);

        // Les bras d'un match ('on' => …) ne sont pas des champs.
        $arguments = preg_replace('/match\s*\(.*?\{.*?\}/s', '', $arguments);
        preg_match_all("/'([a-z_]+)'\s*=>/", $arguments, $cles);

        $classe = 'App\\Models\\'.$modele;
        $instance = new $classe;

        foreach (array_unique($cles[1]) as $cle) {
            if (! $instance->isFillable($cle)) {
                $ignores[] = "$modele.$cle";
            }
        }
    }

    expect($ignores)->toBe([]);
});
