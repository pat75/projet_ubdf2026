<?php
/*
 * Met a jour vues et coeurs des books depuis le serveur de stats legacy
 * (extra-book) quand ses chiffres depassent ceux de la base.
 *
 *   php _scripts/stats-extrabook.php                essai a blanc : rapport seul
 *   php _scripts/stats-extrabook.php --ecrire       met a jour la base
 *   php _scripts/stats-extrabook.php --recommencer  repart du premier book
 *   php _scripts/stats-extrabook.php --login=fixel  un seul book (ni log ni reprise)
 *
 * Compare st_book a users.legacy_views et st_memo a users.legacy_likes (la
 * part extra-book du total ; visit_stats reste a part). Ne baisse jamais un
 * chiffre.
 *
 * Reprenable : _scripts/stats-extrabook.log garde le dernier users.id traite.
 * Rapport des changements (avant -> apres) : _scripts/stats-extrabook-rapport.txt
 */

// Nombre de books par paquet : 10 pour les essais, 100 ensuite.
$PAQUET = 10000;
// Nombre de paquets par lancement (0 = jusqu'au bout).
$MAX_PAQUETS = 0;

const API = 'https://www.extra-book.com/2012_stats/st_action.php';
const LOGINS_PAR_APPEL = 20; // au-dela d'une cinquantaine, le serveur repond 500

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

$ecrire = in_array('--ecrire', $argv, true);
$log = __DIR__.'/stats-extrabook.log';
$rapport = __DIR__.'/stats-extrabook-rapport.txt';

if (in_array('--recommencer', $argv, true)) {
    @unlink($log);
}
$seul = null;
foreach ($argv as $a) {
    if (str_starts_with($a, '--login=')) {
        $seul = substr($a, 8);
    }
}
$dernierId = is_file($log) ? (int) trim(file_get_contents($log)) : 0;

/** Logins d'origine (extra-book ne connait que ceux-la), sinon login actuel. */
function loginsLegacy(array $legacyIds): array
{
    try {
        return DB::connection('legacy')->table('inc_user')->whereIn('us_id', $legacyIds)->pluck('us_login', 'us_id')->all();
    } catch (Throwable) {
        return [];
    }
}

/** JSONP « cb([...]); » : une entree par login, dans l'ordre, false si inconnu. Null si echec. */
function appelStats(array $logins): ?array
{
    $r = Http::retry(2, 1000, throw: false)->timeout(30)->get(API, [
        'action' => 'stats_aff_book',
        'st_champs' => 'st_minibook, st_memo',
        'us_login' => json_encode(array_values($logins)),
    ]);
    if (! $r->successful()) {
        return null;
    }
    $corps = $r->body();
    $debut = strpos($corps, '(');
    $data = json_decode(substr($corps, $debut + 1, strrpos($corps, ')') - $debut - 1), true);

    return is_array($data) && count($data) === count($logins) ? $data : null;
}

$fr = fopen($rapport, 'a');
fwrite($fr, "\n=== ".date('Y-m-d H:i:s').($ecrire ? ' (ecriture)' : ' (essai a blanc)')." depuis id > {$dernierId}\n");

$paquets = 0;
$totaux = ['vus' => 0, 'modifies' => 0, 'inconnus' => 0];

while ($MAX_PAQUETS === 0 || $paquets < $MAX_PAQUETS) {
    $books = User::when($seul, fn ($q) => $q->where('login', $seul), fn ($q) => $q->where('id', '>', $dernierId))
        ->orderBy('id')->limit($PAQUET)
        ->get(['id', 'login', 'legacy_id', 'legacy_views', 'legacy_likes']);
    if ($books->isEmpty()) {
        echo "Termine : tous les books sont passes.\n";
        break;
    }

    $origine = loginsLegacy($books->pluck('legacy_id')->filter()->all());
    $logins = $books->mapWithKeys(fn ($b) => [$b->id => $origine[$b->legacy_id] ?? $b->login]);

    foreach ($books->chunk(LOGINS_PAR_APPEL) as $lot) {
        $noms = $lot->map(fn ($b) => $logins[$b->id])->values()->all();
        // Un lot refuse est repris login par login.
        $stats = appelStats($noms) ?? array_map(fn ($l) => appelStats([$l])[0] ?? null, $noms);

        // Login inconnu : la migration a converti les « _ » du legacy en « - »,
        // et la base legacy n'est pas toujours lisible. On retente en « _ ».
        $retente = [];
        foreach ($noms as $i => $nom) {
            if (! is_array($stats[$i] ?? null) && str_contains($nom, '-')) {
                $retente[$i] = str_replace('-', '_', $nom);
            }
        }
        if ($retente) {
            $bis = appelStats(array_values($retente)) ?? array_map(fn ($l) => appelStats([$l])[0] ?? null, array_values($retente));
            foreach (array_values(array_keys($retente)) as $k => $i) {
                if (is_array($bis[$k] ?? null)) {
                    $stats[$i] = $bis[$k];
                    $logins[$lot->values()[$i]->id] = $retente[$i];
                }
            }
        }

        foreach ($lot->values() as $i => $b) {
            $totaux['vus']++;
            $s = $stats[$i] ?? null;
            if (! is_array($s)) {
                $totaux['inconnus']++;
                fwrite($fr, sprintf("#%d %s (legacy %s, login extra-book %s) : INCONNU d'extra-book, inchange [%d vues et %d coeurs]\n",
                    $b->id, $b->login, $b->legacy_id ?? '-', $logins[$b->id], $b->legacy_views, $b->legacy_likes));
                continue;
            }
            $vues = max((int) $b->legacy_views, (int) ($s['st_book'] ?? 0));
            $coeurs = max((int) $b->legacy_likes, (int) ($s['st_memo'] ?? 0));
            if ($vues === (int) $b->legacy_views && $coeurs === (int) $b->legacy_likes) {
                continue;
            }
            $totaux['modifies']++;
            fwrite($fr, sprintf("#%d %s (legacy %s, login extra-book %s) : vues %d -> %d, coeurs %d -> %d => enregistre : [%d vues et %d coeurs]\n",
                $b->id, $b->login, $b->legacy_id ?? '-', $logins[$b->id], $b->legacy_views, $vues, $b->legacy_likes, $coeurs, $vues, $coeurs));
            if ($ecrire) {
                // Sans evenement, comme ubdf:legacy:accueil.
                User::whereKey($b->id)->update(['legacy_views' => $vues, 'legacy_likes' => $coeurs]);
            }
        }
    }

    $dernierId = $books->last()->id;
    // En essai a blanc, le log n'avance pas : le passage reel refera les memes books.
    if ($seul) {
        break;
    }
    if ($ecrire) {
        file_put_contents($log, $dernierId);
    }
    $paquets++;
    echo "Paquet {$paquets} : jusqu'a l'id {$dernierId}\n";
}

fwrite($fr, "Bilan : {$totaux['vus']} books vus, {$totaux['modifies']} a modifier, {$totaux['inconnus']} inconnus d'extra-book\n");
fclose($fr);

if ($ecrire && $totaux['modifies'] > 0) {
    App\Http\Controllers\Front\AccueilController::viderCache();
}

echo "Bilan : {$totaux['vus']} vus, {$totaux['modifies']} modifies, {$totaux['inconnus']} inconnus. Rapport : {$rapport}\n";
