<?php

/*
 * Compare deux structures MySQL (mysqldump --no-data) et ecrit le plan de
 * mise a jour, sans rien supprimer. Appele par deploy/mise_a_jour_MySQL.sh.
 *
 *   php mise_a_jour_MySQL.php reference.sql prod.sql plan.sql > rapport.txt
 *
 * Regles (conserver toutes les donnees de la prod) :
 *   - table absente de la prod           -> CREATE TABLE
 *   - colonne absente                    -> ADD COLUMN (a sa place)
 *   - colonne differente                 -> MODIFY COLUMN (applique en mode strict :
 *                                           une valeur qui ne tiendrait plus fait echouer)
 *   - index absent / different           -> ADD / DROP + ADD (meme ALTER)
 *   - cle etrangere absente / differente -> ADD / DROP + ADD
 *   - index ou cle presents seulement en prod -> SUPPRIMES (aucune donnee perdue)
 *   - table ou colonne presente seulement en prod -> CONSERVEE, listee
 *
 * Code de sortie : 0 rien a faire, 10 plan a appliquer, 1 erreur.
 */

const LEGACY = '/^(inc_|ub2_|bn_|df2_|nl_|wp_import$)/';
const TECHNIQUES = ['cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs'];

[, $fichierRef, $fichierProd, $fichierPlan] = $argv + [null, null, null, null];
if (! $fichierPlan) {
    fwrite(STDERR, "usage : php mise_a_jour_MySQL.php reference.sql prod.sql plan.sql\n");
    exit(1);
}

/** @return array<string, array{create: string, colonnes: array<string, string>, index: array<string, string>, cles: array<string, string>}> */
function lire(string $fichier): array
{
    preg_match_all('/^CREATE TABLE `([^`]+)` \((.*?)\n\)(.*?);$/sm', file_get_contents($fichier), $m, PREG_SET_ORDER);
    $tables = [];

    foreach ($m as [$create, $nom, $corps]) {
        $t = ['create' => preg_replace('/ AUTO_INCREMENT=\d+/', '', $create), 'colonnes' => [], 'index' => [], 'cles' => []];

        foreach (explode("\n", trim($corps)) as $ligne) {
            $ligne = rtrim(trim($ligne), ',');
            if (preg_match('/^`([^`]+)` (.*)$/', $ligne, $c)) {
                $t['colonnes'][$c[1]] = $c[2];
            } elseif (str_starts_with($ligne, 'PRIMARY KEY')) {
                $t['index']['PRIMARY'] = $ligne;
            } elseif (preg_match('/^(?:UNIQUE |FULLTEXT |SPATIAL )?KEY `([^`]+)`/', $ligne, $c)) {
                $t['index'][$c[1]] = $ligne;
            } elseif (preg_match('/^CONSTRAINT `([^`]+)` FOREIGN KEY/', $ligne, $c)) {
                $t['cles'][$c[1]] = $ligne;
            }
        }
        $tables[$nom] = $t;
    }

    return $tables;
}

/** Ecritures equivalentes MySQL 5.7 (local) / MariaDB 11 (O2switch) ramenees a une seule. */
function normaliser(string $def): string
{
    $d = strtolower($def);
    if (str_contains($d, 'json_valid(')) {
        $d = 'json'.(str_contains($d, 'not null') ? ' not null' : '');
    }
    $d = preg_replace('/\b(tinyint|smallint|mediumint|bigint|int)\(\d+\)/', '$1', $d);
    $d = str_replace('current_timestamp()', 'current_timestamp', $d);
    $d = preg_replace('/ (character set|collate) \w+/', '', $d);
    $d = preg_replace("/default '(-?\\d+(\\.\\d+)?)'/", 'default $1', $d);
    $d = preg_replace('/ default null\b/', '', $d);
    $d = str_replace(' using btree', '', $d);

    return trim(preg_replace('/\s+/', ' ', $d));
}

/** Type seul : un MODIFY qui le change est signale. */
function typeDe(string $def): string
{
    preg_match('/^\S+( unsigned)?/', normaliser($def), $m);

    return $m[0];
}

$ref = lire($fichierRef);
$prod = lire($fichierProd);
$plan = [];
$rapport = [];
$conserves = [];
$risques = 0;

foreach ($ref as $table => $r) {
    if (in_array($table, TECHNIQUES, true) || preg_match(LEGACY, $table)) {
        continue;
    }

    if (! isset($prod[$table])) {
        $rapport[$table][] = '+ table creee ('.count($r['colonnes']).' colonnes)';
        $plan[] = ['table' => $table, 'sql' => "SET FOREIGN_KEY_CHECKS=0;\n{$r['create']}\nSET FOREIGN_KEY_CHECKS=1;"];

        continue;
    }

    $p = $prod[$table];
    $alter = [];
    $precedente = null;

    // Index absents de la reference : retires (une donnee n'est jamais dans un index).
    foreach (array_diff_key($p['index'], $r['index']) as $nom => $def) {
        $alter[] = "DROP INDEX `{$nom}`";
        $rapport[$table][] = "- index {$def}";
    }

    foreach ($r['colonnes'] as $col => $def) {
        $place = $precedente ? "AFTER `{$precedente}`" : 'FIRST';
        if (! isset($p['colonnes'][$col])) {
            $alter[] = "ADD COLUMN `{$col}` {$def} {$place}";
            $rapport[$table][] = "+ colonne {$col} : {$def}";
        } elseif (normaliser($def) !== normaliser($p['colonnes'][$col])) {
            $alter[] = "MODIFY COLUMN `{$col}` {$def}";
            $risque = typeDe($def) !== typeDe($p['colonnes'][$col]);
            $risques += (int) $risque;
            $rapport[$table][] = "~ colonne {$col} : {$p['colonnes'][$col]}\n        -> {$def}".($risque ? '   [TYPE CHANGE]' : '');
        }
        $precedente = $col;
    }

    foreach ($r['index'] as $nom => $def) {
        if (! isset($p['index'][$nom])) {
            $alter[] = "ADD {$def}";
            $rapport[$table][] = "+ index {$def}";
        } elseif (normaliser($def) !== normaliser($p['index'][$nom])) {
            $alter[] = $nom === 'PRIMARY' ? 'DROP PRIMARY KEY' : "DROP INDEX `{$nom}`";
            $alter[] = "ADD {$def}";
            $rapport[$table][] = "~ index {$p['index'][$nom]}\n        -> {$def}";
        }
    }

    if ($alter !== []) {
        $plan[] = ['table' => $table, 'sql' => "ALTER TABLE `{$table}`\n  ".implode(",\n  ", $alter).';'];
    }

    foreach (array_keys(array_diff_key($p['colonnes'], $r['colonnes'])) as $col) {
        $conserves[] = "{$table}.{$col} (colonne)";
    }
}

// Cles etrangeres en dernier : toutes les tables et colonnes existent alors.
foreach ($ref as $table => $r) {
    if (! isset($prod[$table]) || preg_match(LEGACY, $table)) {
        continue;
    }
    $p = $prod[$table];
    foreach ($r['cles'] as $nom => $def) {
        if (! isset($p['cles'][$nom])) {
            $plan[] = ['table' => $table, 'sql' => "ALTER TABLE `{$table}` ADD {$def};"];
            $rapport[$table][] = "+ cle etrangere {$def}";
        } elseif (normaliser($def) !== normaliser($p['cles'][$nom])) {
            $plan[] = ['table' => $table, 'sql' => "ALTER TABLE `{$table}` DROP FOREIGN KEY `{$nom}`, ADD {$def};"];
            $rapport[$table][] = "~ cle etrangere {$p['cles'][$nom]}\n        -> {$def}";
        }
    }
    foreach (array_diff_key($p['cles'], $r['cles']) as $nom => $def) {
        $plan[] = ['table' => $table, 'sql' => "ALTER TABLE `{$table}` DROP FOREIGN KEY `{$nom}`;"];
        $rapport[$table][] = "- cle etrangere {$def}";
    }
}

foreach ($prod as $table => $p) {
    if (! isset($ref[$table]) && ! preg_match(LEGACY, $table) && ! in_array($table, TECHNIQUES, true)) {
        $conserves[] = "{$table} (table entiere, ".count($p['colonnes']).' colonnes)';
    }
}

echo "MODIFICATIONS PAR TABLE (+ ajout, ~ modification, - index ou cle retire)\n";
if ($rapport === []) {
    echo "  aucune : la structure de la prod est deja celle de la reference\n";
}
ksort($rapport);
foreach ($rapport as $table => $lignes) {
    echo "\n  {$table}\n";
    foreach ($lignes as $l) {
        echo "    {$l}\n";
    }
}
echo "\nCONSERVE EN PROD (absent de la reference, jamais supprime)\n";
echo $conserves === [] ? "  rien\n" : '  '.implode("\n  ", $conserves)."\n";
printf("\nSYNTHESE : %d table(s) touchee(s), %d instruction(s), %d changement(s) de type\n", count($rapport), count($plan), $risques);

$sortie = '';
foreach ($plan as $i => $etape) {
    $sortie .= sprintf("SELECT '   [%d/%d] %s' AS '';\n%s\n", $i + 1, count($plan), $etape['table'], $etape['sql']);
}
file_put_contents($fichierPlan, $sortie);

exit($plan === [] ? 0 : 10);
